<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\ShoeType;
use App\Models\Brand;
use Illuminate\Support\Facades\Storage;
use App\Models\ProductVariant;



class ProductController extends Controller
{
    public function index(Request $request)
{
    $search = trim($request->search);
    $category = $request->category;
    $brand = $request->brand;
    $shoeType = $request->shoe_type;

    $products = Product::with(['category', 'brand', 'shoeType', 'variants'])
        ->where('is_active', true)

        ->when($search, function ($query) use ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(product_name) LIKE ?', ['%' . strtolower($search) . '%'])
                  ->orWhereRaw('LOWER(product_description) LIKE ?', ['%' . strtolower($search) . '%'])
                  ->orWhereHas('brand', function ($brandQuery) use ($search) {
                      $brandQuery->whereRaw('LOWER(brand_name) LIKE ?', ['%' . strtolower($search) . '%']);
                  })
                  ->orWhereHas('category', function ($categoryQuery) use ($search) {
                      $categoryQuery->whereRaw('LOWER(category_name) LIKE ?', ['%' . strtolower($search) . '%']);
                  })
                  ->orWhereHas('shoeType', function ($shoeTypeQuery) use ($search) {
                      $shoeTypeQuery->whereRaw('LOWER(shoe_type_name) LIKE ?', ['%' . strtolower($search) . '%']);
                  });
            });
        })

        ->when($category, function ($query) use ($category) {
            $query->where('category_id', $category);
        })

        ->when($brand, function ($query) use ($brand) {
            $query->where('brand_id', $brand);
        })

        ->when($shoeType, function ($query) use ($shoeType) {
            $query->where('shoe_type_id', $shoeType);
        })

        ->orderBy('product_name')
        ->paginate(5)
        ->withQueryString();

    $categories = Category::where('is_active', true)
        ->orderBy('category_name')
        ->get();

    $brands = Brand::where('is_active', true)
        ->orderBy('brand_name')
        ->get();

    $shoeTypes = ShoeType::where('is_active', true)
        ->orderBy('display_order')
        ->get();

    return view('admin.products.index', [
        'products' => $products,
        'categories' => $categories,
        'brands' => $brands,
        'shoeTypes' => $shoeTypes,
        'search' => $search,

        'totalProducts' => Product::where('is_active', true)->count(),
        'totalVariants' => ProductVariant::count(),
    ]);
}
 public function store(Request $request)
{
    // Normalize product name
    $request->merge([
        'product_name' => ucwords(strtolower(preg_replace('/\s+/', ' ', trim($request->product_name))))
    ]);

    $validated = $request->validate([
        'product_name' => 'required|string|max:255',
        'product_description' => 'nullable|string',
        'category_id' => 'required|exists:categories,category_id',
        'brand_id' => 'required|exists:brands,brand_id',
        'shoe_type_id' => 'required|exists:shoe_types,shoe_type_id',
        'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        'primary_image' => 'nullable|integer',
    ]);

    // Prevent duplicate product names (case-insensitive)
    $exists = Product::whereRaw('LOWER(product_name) = ?', [
        strtolower($validated['product_name'])
    ])->exists();

    if ($exists) {
        return back()
            ->withErrors([
                'product_name' => 'This product already exists.'
            ])
            ->withInput();
    }

    try {
        $product = Product::create([
            'product_name' => $validated['product_name'],
            'product_description' => $validated['product_description'],
            'category_id' => $validated['category_id'],
            'brand_id' => $validated['brand_id'],
            'shoe_type_id' => $validated['shoe_type_id'],
        ]);

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
    $product->update([
        'is_active' => false
    ]);

    return redirect()
        ->route('admin.products.index')
        ->with('success', 'Product archived successfully!');
}
 // Show the edit form
public function edit(Product $product)
{
    $categories = Category::where('is_active', true)->get();
    $brands = Brand::where('is_active', true)->get();
    $shoeTypes = ShoeType::where('is_active', true)
        ->orderBy('display_order')
        ->get();

    return view('admin.products.edit', compact(
        'product',
        'categories',
        'brands',
        'shoeTypes'
    ));
}

// Update the product
public function update(Request $request, Product $product)
{
    // Normalize product name
    $request->merge([
        'product_name' => ucwords(strtolower(preg_replace('/\s+/', ' ', trim($request->product_name))))
    ]);

    $validated = $request->validate([
        'product_name' => 'required|string|max:255',
        'product_description' => 'nullable|string',
        'category_id' => 'required|exists:categories,category_id',
        'brand_id' => 'required|exists:brands,brand_id',
        'shoe_type_id' => 'required|exists:shoe_types,shoe_type_id',
        'images' => 'nullable',
        'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        'primary_image' => 'nullable|integer',
    ]);

    // Prevent duplicate names except this product
    $exists = Product::whereRaw('LOWER(product_name) = ?', [
        strtolower($validated['product_name'])
    ])
    ->where('product_id', '!=', $product->product_id)
    ->exists();

    if ($exists) {
        return back()
            ->withErrors([
                'product_name' => 'This product already exists.'
            ])
            ->withInput();
    }

    try {
        $product->update([
            'product_name' => $validated['product_name'],
            'product_description' => $validated['product_description'],
            'category_id' => $validated['category_id'],
            'brand_id' => $validated['brand_id'],
            'shoe_type_id' => $validated['shoe_type_id'],
        ]);

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
