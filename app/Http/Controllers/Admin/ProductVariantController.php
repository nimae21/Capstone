<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\ApprovalService;
use App\Services\ProductImageService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductVariantController extends Controller
{
    public function __construct(
        protected ProductImageService $imageService
    ) {}

    /**
     * Display all variants of a product.
     */
    public function index(Product $product)
    {
        $variants = ProductVariant::where('product_id', $product->product_id)
            ->where('is_active', true)
            ->orderByRaw('CAST(size AS DECIMAL(4,1))')
            ->get();

        $product->load('images');

        return view('admin.variants.index', compact('product', 'variants'));
    }

    /**
     * Store a new product variant.
     */
    public function store(Request $request, Product $product)
    {
        return app(ApprovalService::class)->submit($request, 'variant', ['product_id' => $product->product_id]);
    }

    /**
     * Show the edit form.
     */
    public function edit(ProductVariant $variant)
    {
        return view('admin.variants.edit', compact('variant'));
    }

    /**
     * Update a product variant.
     */
    public function update(Request $request, ProductVariant $variant)
    {
        $request->validate([
            'size' => ['required', Rule::in(self::availableSizes())],
            'color' => 'required|string|max:50',
        ]);

        $color = ucwords(strtolower(trim($request->color)));

        $exists = ProductVariant::where('product_id', $variant->product_id)
            ->where('size', $request->size)
            ->where('color', $color)
            ->where('product_variant_id', '!=', $variant->product_variant_id)
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->withErrors([
                    'color' => 'Another variant already uses this color and size.',
                ]);
        }

        $variant->update([
            'size' => $request->size,
            'color' => $color,
        ]);

        return redirect()
            ->route('admin.products.variants.index', $variant->product_id)
            ->with('success', 'Variant updated successfully!');
    }

    /**
     * Archive a variant.
     */
    public function destroy(ProductVariant $variant)
    {
        $variant->update([
            'is_active' => false,
        ]);

        return back()->with(
            'success',
            'Variant archived successfully.'
        );
    }

    private static function availableSizes(): array
    {
        return collect(range(14, 26, 1))
            ->map(function (int $size): string {
                $value = $size / 2;

                return fmod($value, 1) === 0.0
                    ? (string) (int) $value
                    : number_format($value, 1, '.', '');
            })
            ->all();
    }
}
