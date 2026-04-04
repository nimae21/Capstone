<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductVariant;
use App\Models\Product;

class ProductVariantController extends Controller
{
    // Show all variants of a product
    public function index(Product $product)
    {
        $variants = $product->variants;
        return view('admin.variants.index', compact('product', 'variants'));
    }

    // Store a new variant
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'size' => 'required|string',
            'color' => 'required|string',
        ]);

        ProductVariant::create([
            'product_id' => $product->product_id,
            'size' => $request->size,
            'color' => $request->color,
        ]);

        return redirect()->route('admin.variants.index', $product->product_id);
    }

    // Delete a variant
    public function destroy(ProductVariant $variant)
    {
        $variant->delete();
        return back();
    }
}

