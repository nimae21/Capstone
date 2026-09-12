<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApprovalRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShoeType;
use App\Services\ApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * The Super Admin approval queue. Reviewing goes through ApprovalService, so
 * the mobile app applies exactly the same validation, locking and audit rules
 * as the website queue - it is a second client, not a second workflow.
 */
class ApprovalController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:pending,approved,rejected'],
            'search' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $query = ApprovalRequest::with(['requester', 'reviewer'])
            ->where('status', $filters['status'] ?? 'pending');

        if (! empty($filters['search'])) {
            $term = trim($filters['search']);
            $query->where(function ($q) use ($term) {
                $q->where('entity_type', 'like', '%'.$term.'%')
                    ->orWhere('id', ctype_digit($term) ? (int) $term : 0)
                    ->orWhereHas('requester', fn ($u) => $u
                        ->where('first_name', 'like', '%'.$term.'%')
                        ->orWhere('last_name', 'like', '%'.$term.'%')
                        ->orWhere('email', 'like', '%'.$term.'%'));
            });
        }

        $page = $query->orderByDesc('id')->paginate(20)
            ->through(fn (ApprovalRequest $item) => $this->present($item))
            ->toArray();

        return response()->json($page + ['counts' => $this->statusCounts()]);
    }

    public function show(ApprovalRequest $approval)
    {
        $approval->load(['requester', 'reviewer']);

        return response()->json($this->present($approval, true));
    }

    public function counts()
    {
        return response()->json($this->statusCounts());
    }

    private function statusCounts(): array
    {
        $row = ApprovalRequest::query()->selectRaw(
            "COALESCE(SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END), 0) as pending,
             COALESCE(SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END), 0) as approved,
             COALESCE(SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END), 0) as rejected"
        )->first();

        return [
            'pending' => (int) $row->pending,
            'approved' => (int) $row->approved,
            'rejected' => (int) $row->rejected,
        ];
    }

    public function review(Request $request, ApprovalService $service)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:100'],
            'ids.*' => ['required', 'integer', 'distinct', 'exists:approval_requests,id'],
            'decision' => ['required', 'in:approved,rejected'],
            'reason' => ['nullable', 'required_if:decision,rejected', 'string', 'max:2000'],
        ]);

        $reviewed = [];
        $failed = [];

        foreach (array_chunk($data['ids'], 25) as $chunk) {
            foreach ($chunk as $id) {
                try {
                    $service->review((int) $id, $request->user(), $data['decision'], $data['reason'] ?? null);
                    $reviewed[] = (int) $id;
                } catch (ValidationException $e) {
                    $failed[] = ['id' => (int) $id, 'message' => implode(' ', $e->validator->errors()->all())];
                } catch (\Throwable $e) {
                    report($e);
                    $failed[] = ['id' => (int) $id, 'message' => 'The database could not apply this request. It remains pending; refresh and retry.'];
                }
            }
        }

        $message = count($reviewed).' request(s) '.($data['decision'] === 'approved' ? 'approved' : 'rejected').'.';
        if ($failed) {
            $message .= ' '.count($failed).' could not be applied.';
        }

        return response()->json([
            'message' => $message,
            'reviewed' => $reviewed,
            'failed' => $failed,
        ]);
    }

    private function present(ApprovalRequest $item, bool $withPayload = false): array
    {
        $payload = $item->payload ?? [];

        $presented = [
            'id' => $item->id,
            'entity_type' => $item->entity_type,
            'entity_label' => ucfirst(str_replace('_', ' ', (string) $item->entity_type)),
            'action_type' => $item->action_type,
            'status' => $item->status,
            'summary' => $this->summary($item, $payload),
            'requester' => $item->requester ? [
                'id' => $item->requester->id,
                'name' => $item->requester->full_name,
                'email' => $item->requester->email,
                'initials' => $item->requester->initials,
            ] : null,
            'reviewer' => $item->reviewer?->full_name,
            'submitted_at' => $item->created_at?->toIso8601String(),
            'reviewed_at' => $item->reviewed_at?->toIso8601String(),
            'rejection_reason' => $item->rejection_reason,
            'image_count' => count($payload['_images'] ?? []),
        ];

        if ($withPayload) {
            $presented['fields'] = $this->fields($item->entity_type, $payload);
            $presented['images'] = collect($payload['_images'] ?? [])
                ->map(fn ($path) => ['path' => $path, 'url' => Storage::disk('supabase')->url($path)])
                ->values();
            $presented['raw_payload'] = collect($payload)->except('_images');
        }

        return $presented;
    }

    private function summary(ApprovalRequest $item, array $payload): string
    {
        $name = null;
        foreach (['product_name', 'category_name', 'brand_name', 'shoe_type_name'] as $key) {
            if (! empty($payload[$key])) {
                $name = (string) $payload[$key];
                break;
            }
        }

        if ($item->entity_type === 'variant') {
            $name = trim(($payload['size'] ?? '?').' / '.($payload['color'] ?? '?'));
        }

        $label = ucfirst(str_replace('_', ' ', (string) $item->entity_type));

        return trim($label.($name ? ': '.$name : ' #'.$item->id));
    }

    /**
     * Human labels and resolved names for the stored payload, so the phone
     * shows "Category: Running" instead of "category_id: 4".
     */
    private function fields(string $type, array $payload): array
    {
        $labels = [
            'product_name' => 'Product name',
            'product_description' => 'Description',
            'category_id' => 'Category',
            'brand_id' => 'Brand',
            'shoe_type_id' => 'Shoe type',
            'new_arrival_until' => 'New arrival until',
            'product_id' => 'Product',
            'product_variant_id' => 'Variant',
            'size' => 'Size',
            'color' => 'Color',
            'received_quantity' => 'Received quantity',
            'price' => 'Price',
            'deliver_date' => 'Delivery date',
            'category_name' => 'Category name',
            'category_description' => 'Description',
            'brand_name' => 'Brand name',
            'shoe_type_name' => 'Shoe type name',
            'description' => 'Description',
        ];

        $fields = [];
        foreach ($payload as $key => $value) {
            if ($key === '_images' || $value === null || $value === '') {
                continue;
            }

            $fields[] = [
                'key' => $key,
                'label' => $labels[$key] ?? ucfirst(str_replace('_', ' ', (string) $key)),
                'value' => $this->resolveValue($key, $value),
            ];
        }

        return $fields;
    }

    private function resolveValue(string $key, $value): string
    {
        if (is_array($value)) {
            return json_encode($value);
        }

        $resolved = match ($key) {
            'category_id' => Category::whereKey($value)->value('category_name'),
            'brand_id' => Brand::whereKey($value)->value('brand_name'),
            'shoe_type_id' => ShoeType::whereKey($value)->value('shoe_type_name'),
            'product_id' => Product::whereKey($value)->value('product_name'),
            default => null,
        };

        if ($key === 'product_variant_id') {
            $variant = ProductVariant::with('product')->whereKey($value)->first();
            if ($variant) {
                return trim(($variant->product?->product_name ?? 'Product').' - size '.$variant->size.' / '.$variant->color);
            }
        }

        return $resolved !== null ? (string) $resolved : (string) $value;
    }
}