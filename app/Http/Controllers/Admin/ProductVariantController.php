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
        // Eager load variants for efficiency
        $product->load('variants');

        // Pass only the product, variants are accessed via $product->variants in blade
        return view('admin.variants.index', compact('product'));
    }

    // Store a new variant
    public function store(Request $request, Product $product)
    {
        // Validate form inputs
        $request->validate([
            'size' => 'required|string|max:10',
            'color' => 'required|string|max:50',
        ]);

        // Create the variant using the relationship
        $product->variants()->create([
            'size' => $request->size,
            'color' => $request->color,
        ]);

        return redirect()->route('admin.products.variants.index', $product->product_id)
                         ->with('success', 'Variant added successfully!');
    }

    // Show the edit form for a variant
public function edit(ProductVariant $variant)
{
    return view('admin.variants.edit', compact('variant'));
}

// Update the variant in the database
public function update(Request $request, ProductVariant $variant)
{
    // Validate the inputs
    $request->validate([
        'size' => 'required|string|max:10',
        'color' => 'required|string|max:50',
    ]);

    // Update the variant
    $variant->update([
        'size' => $request->size,
        'color' => $request->color,
    ]);

    // Redirect back to the product variants page
    return redirect()->route('admin.products.variants.index', $variant->product_id)
                     ->with('success', 'Variant updated successfully!');
}

    // Delete a variant
    public function destroy(ProductVariant $variant)
    {
        $variant->delete();
        return back()->with('success', 'Variant deleted successfully!');
    }
}

