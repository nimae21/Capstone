<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class ProductVariantController extends Controller
{
    /**
     * Display all variants of a product.
     */
    public function index(Product $product)
    {
        $variants = ProductVariant::where('product_id', $product->product_id)
            ->where('is_active', true)
            ->orderBy('size')
            ->get();

        return view('admin.variants.index', compact('product', 'variants'));
    }

    /**
     * Store a new product variant.
     */
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'size'  => 'required|integer|min:1',
            'color' => 'required|string|max:50',
        ]);

        $color = ucwords(strtolower(trim($request->color)));

        $exists = ProductVariant::where('product_id', $product->product_id)
            ->where('size', $request->size)
            ->where('color', $color)
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->withErrors([
                    'color' => 'This color and size already exists for this product.',
                ]);
        }

        $product->variants()->create([
            'size'  => $request->size,
            'color' => $color,
        ]);

        return redirect()
            ->route('admin.products.variants.index', $product->product_id)
            ->with('success', 'Variant added successfully!');
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
            'size'  => 'required|integer|min:1',
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
            'size'  => $request->size,
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
}