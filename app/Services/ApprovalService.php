<?php

namespace App\Services;

use App\Models\ApprovalRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\ShoeType;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ApprovalService
{
    private const MODELS = ['category' => Category::class, 'brand' => Brand::class, 'shoe_type' => ShoeType::class,
        'product' => Product::class, 'variant' => ProductVariant::class, 'stock' => Stock::class, 'images' => ProductImage::class];

    private function rules(string $type): array
    {
        return match ($type) {
            'category' => ['category_name' => 'required|string|max:255', 'category_description' => 'nullable|string|max:255'],
            'brand' => ['brand_name' => 'required|string|max:255'],
            'shoe_type' => ['shoe_type_name' => 'required|string|max:255', 'description' => 'nullable|string|max:10000'],
            'product' => ['product_name' => 'required|string|max:255', 'product_description' => 'nullable|string|max:10000',
                'category_id' => 'required|exists:categories,category_id', 'brand_id' => 'required|exists:brands,brand_id',
                'shoe_type_id' => 'required|exists:shoe_types,shoe_type_id', 'new_arrival_until' => 'sometimes|nullable|date_format:Y-m-d'],
            'variant' => ['product_id' => 'required|exists:products,product_id', 'size' => ['required', Rule::in(array_map(fn ($n) => (string) ($n / 2), range(14, 26)))], 'color' => 'required|string|max:50'],
            'stock' => ['product_variant_id' => 'required|exists:product_variants,product_variant_id', 'received_quantity' => 'required|integer|min:1|max:2147483647',
                'price' => 'required|numeric|min:0.01|max:99999999.99', 'deliver_date' => 'required|date|before_or_equal:today'],
            'images' => ['product_id' => 'required|exists:products,product_id', 'color' => 'nullable|string|max:50'],
            default => throw ValidationException::withMessages(['request' => 'Unsupported request type.']),
        };
    }

    private function validatePayload(string $type, array $payload): array
    {
        $data = Validator::make($payload, $this->rules($type))->validate();
        $name = match ($type) {
            'category' => 'category_name','brand' => 'brand_name','shoe_type' => 'shoe_type_name','product' => 'product_name',default => null
        };
        if ($name) {
            $data[$name] = ucwords(strtolower(preg_replace('/\s+/', ' ', trim($data[$name]))));
            if (self::MODELS[$type]::whereRaw('LOWER('.$name.') = ?', [strtolower($data[$name])])->exists()) {
                throw ValidationException::withMessages([$name => 'This name already exists.']);
            }
        }
        if ($type === 'variant') {
            $data['color'] = ucwords(strtolower(trim($data['color'])));
            if (ProductVariant::where('product_id', $data['product_id'])->where('size', $data['size'])->where('color', $data['color'])->exists()) {
                throw ValidationException::withMessages(['color' => 'This color and size already exists for this product.']);
            }
        }
        foreach (['category_id' => Category::class, 'brand_id' => Brand::class, 'shoe_type_id' => ShoeType::class, 'product_id' => Product::class, 'product_variant_id' => ProductVariant::class] as $key => $model) {
            if (isset($data[$key]) && ! $model::whereKey($data[$key])->where('is_active', true)->exists()) {
                throw ValidationException::withMessages([$key => 'The selected record is inactive.']);
            }
        }

        return $data;
    }

    public function submit(Request $request, string $type, array $parent = [])
    {
        abort_unless($request->user()?->role === 'admin' && $request->user()->is_active, 403);
        $data = $this->validatePayload($type, array_merge($request->all(), $parent));
        $request->validate(['images' => 'sometimes|array|max:10', 'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120']);
        $files = $request->file('images', []);
        if ($request->hasFile('image')) {
            $files[] = $request->file('image');
        }
        if (count($files) > 10) {
            throw ValidationException::withMessages(['images' => 'Upload at most 10 images per request.']);
        }
        if ($files && ! in_array($type, ['product', 'variant', 'images'], true)) {
            throw ValidationException::withMessages(['images' => 'Images can only be submitted for products and variants.']);
        }
        if ($type === 'images' && ! $files) {
            throw ValidationException::withMessages(['images' => 'Select at least one image.']);
        }
        $paths = [];
        try {
            foreach ($files as $file) {
                $path = $file->store('products/pending', 'supabase');
                if (! $path) {
                    throw new \RuntimeException('Image staging failed.');
                }
                $paths[] = $path;
            }
            $data['_images'] = $paths;
            $approval = ApprovalRequest::create(['requester_id' => $request->user()->id, 'entity_type' => $type, 'payload' => $data]);
        } catch (\Throwable $e) {
            foreach ($paths as $path) {
                Storage::disk('supabase')->delete($path);
            }
            throw $e;
        }
        $message = 'Request #'.$approval->id.' submitted for Super Admin approval. Nothing has been added to the live catalog yet.';
        if ($request->expectsJson()) {
            return response()->json(['pending' => true, 'request_id' => $approval->id, 'message' => $message], 202);
        }

        return back()->with('success', $message);
    }

    public function review(int $id, User $reviewer, string $decision, ?string $reason = null): void
    {
        abort_unless($reviewer->role === 'super_admin' && $reviewer->is_active, 403);
        Validator::make(['decision' => $decision, 'reason' => $reason], ['decision' => 'required|in:approved,rejected', 'reason' => 'nullable|required_if:decision,rejected|string|max:2000'])->validate();
        DB::transaction(function () use ($id, $reviewer, $decision, $reason) {
            // A stable database mutex serializes competing approvals, including duplicate names.
            // Audit requests are never deleted; lock the oldest row before the target row.
            ApprovalRequest::orderBy('id')->lockForUpdate()->firstOrFail();
            $item = ApprovalRequest::whereKey($id)->lockForUpdate()->firstOrFail();
            if ((int) $item->requester_id === (int) $reviewer->id || $item->status !== 'pending') {
                throw ValidationException::withMessages(['request' => 'This request is already reviewed or belongs to you.']);
            }
            if ($decision === 'approved') {
                if (! User::whereKey($item->requester_id)->where('role', 'admin')->where('is_active', true)->exists()) {
                    throw ValidationException::withMessages(['request' => 'The requesting admin is no longer active. Reject this request or restore their access first.']);
                }
                $type = $item->entity_type;
                $data = $this->validatePayload($type, $item->payload);
                if ($type === 'stock') {
                    $data['remaining_quantity'] = $data['received_quantity'];
                }
                if ($type === 'shoe_type') {
                    $data['display_order'] = 0;
                }
                if ($type === 'product' && ! empty($data['new_arrival_until'])) {
                    $data['new_arrival_until'] = Carbon::parse($data['new_arrival_until'])->endOfDay();
                }
                $entity = $type === 'images' ? Product::findOrFail($data['product_id']) : self::MODELS[$type]::create($data);
                if ($type === 'stock') {
                    StockMovement::create(['stock_id' => $entity->getKey(), 'quantity' => $data['received_quantity'], 'type' => 'in']);
                }
                $productId = $type === 'product' ? $entity->getKey() : ($data['product_id'] ?? null);
                if ($productId) {
                    $product = Product::whereKey($productId)->lockForUpdate()->firstOrFail();
                    $order = $product->images()->max('display_order') ?? 0;
                    $primary = $product->images()->where('is_primary', true)->exists();
                    foreach ($item->payload['_images'] ?? [] as $path) {
                        ProductImage::create(['product_id' => $productId, 'image_path' => $path, 'color' => $data['color'] ?? null, 'display_order' => ++$order, 'is_primary' => ! $primary]);
                        $primary = true;
                    }
                }
                $item->entity_id = $entity->getKey();
            }
            $item->fill(['status' => $decision, 'reviewer_id' => $reviewer->id, 'reviewed_at' => now(), 'rejection_reason' => $decision === 'rejected' ? $reason : null])->save();
        }, 3);
    }
}
