<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Storage;



class ProductController extends Controller
{
    public function index(){
 $products = Product::with(['category', 'brand'])->paginate(5);

        $categories = Category::all();
        $brands = Brand::all();
        return view('admin.products.index', compact('products', 'categories', 'brands'));
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
        if ($request->hasFile('images')) {
            $this->saveProductImages($product, $request->file('images'), $validated['primary_image'] ?? 0);
        }

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
        if ($request->hasFile('images')) {
            $this->saveProductImages($product, $request->file('images'), $validated['primary_image'] ?? 0);
        }

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product updated successfully!');
    } catch (\Exception $e) {
        return back()
            ->withInput()
            ->with('error', 'Failed to update product: ' . $e->getMessage());
    }
}

/**
 * Save product images
 * @param Product $product
 * @param array $images
 * @param int $primaryIndex Index of primary image
 */
private function saveProductImages(Product $product, $images, $primaryIndex = 0)
{
    $displayOrder = $product->images->max('display_order') ?? 0;

    foreach ($images as $index => $image) {
        if ($image instanceof \Illuminate\Http\UploadedFile) {
            // Store the image
            $filename = uniqid() . '_' . time() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('public/products', $filename);

            // Create database record
            ProductImage::create([
                'product_id' => $product->product_id,
                'image_path' => 'storage/products/' . $filename,
                'is_primary' => ($index == $primaryIndex),
                'display_order' => $displayOrder + $index + 1,
            ]);
        }
    }
}

    
}
