<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Support\Facades\Storage;
use App\Models\ProductVariant;



class ProductController extends Controller
{
    public function index(Request $request)
{
    $search = $request->input('search');

    $products = Product::with(['category', 'brand','variants'])
        ->when($search, function ($query, $search) {
            $query->whereRaw('LOWER(product_name) LIKE ?', ['%' . strtolower($search) . '%'])
                  ->orWhereRaw('LOWER(product_description) LIKE ?', ['%' . strtolower($search) . '%']);
        })
        ->paginate(5)
        ->withQueryString();

    $categories = Category::all();
    $brands = Brand::all();
    $totalVariants = ProductVariant::count();

    return view('admin.products.index', compact('products', 'categories', 'brands', 'search', 'totalVariants'));
}
 public function store(Request $request)
{
    $validated = $request->validate([
        'product_name' => 'required|string|max:255',
        'product_description' => 'nullable|string',
        'category_id' => 'required|exists:categories,category_id',
        'brand_id' => 'required|exists:brands,brand_id',
        'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max per image
        'primary_image' => 'nullable|integer',
    ]);

    try {
        $product = Product::create([
            'product_name' => $validated['product_name'],
            'product_description' => $validated['product_description'],
            'category_id' => $validated['category_id'],
            'brand_id' => $validated['brand_id'],
        ]);

        // Handle image uploads
        // Image upload is now handled by ProductImageController.

        return redirect()
            ->route('admin.products.index')
            ->with('success', "Product '{$product->product_name}' created successfully!");
    } catch (\Exception $e) {
        return back()
            ->withInput()
            ->with('error', 'Failed to create product: ' . $e->getMessage());
    }
}

 public function destroy(Product $product)
{
    // Delete all associated images
    foreach ($product->images as $image) {
        if (Storage::exists('public/products/' . basename($image->image_path))) {
            Storage::delete('public/products/' . basename($image->image_path));
        }
        $image->delete();
    }

    $product->delete();
    return redirect()
        ->route('admin.products.index')
        ->with('success', 'Product deleted successfully!');
}
 // Show the edit form
public function edit(Product $product)
{
    $categories = Category::all();
    $brands = Brand::all();
    return view('admin.products.edit', compact('product', 'categories', 'brands'));
}

// Update the product
public function update(Request $request, Product $product)
{
    $validated = $request->validate([
        'product_name' => 'required|string|max:255',
        'product_description' => 'nullable|string',
        'category_id' => 'required|exists:categories,category_id',
        'brand_id' => 'required|exists:brands,brand_id',
        'images' => 'nullable',
        'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        'primary_image' => 'nullable|integer',
    ]);

    try {
        $product->update([
            'product_name' => $validated['product_name'],
            'product_description' => $validated['product_description'],
            'category_id' => $validated['category_id'],
            'brand_id' => $validated['brand_id'],
        ]);

        // Handle new image uploads
        // Image upload is now handled by ProductImageController.

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product updated successfully!');
    } catch (\Exception $e) {
        return back()
            ->withInput()
            ->with('error', 'Failed to update product: ' . $e->getMessage());
    }
}
}
