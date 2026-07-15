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
    
    $variants = ProductVariant::where('product_id', $product->product_id)
    ->where('is_active', true)
        ->orderBy('size', 'asc')
        ->get();

    return view('admin.variants.index', compact('product', 'variants'));
}
    // Store a new variant
    public function store(Request $request, Product $product)
{
    $request->validate([
    'size' => 'required|integer|min:1',
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
            'color' => 'This color and size already exists for this product.'
        ]);

}

    $product->variants()->create([
    'size' => $request->size,
    'color' => $color,
]);

    return redirect()
        ->route('admin.products.variants.index', $product->product_id)
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
       $request->validate([
    'size' => 'required|integer|min:1',
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
            'color' => 'Another variant already uses this color and size.'
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

    // Delete a variant
    public function destroy(ProductVariant $variant)
    {
        $variant->update([
    'is_active' => false
]);
        return back()->with('success', 'Variant archived successfully!');
    }
}

