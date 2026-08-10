<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShoeType;
use Illuminate\Http\Request;
use App\Services\ProductImageService;  

class ProductController extends Controller
{
    public function __construct(
        protected ProductImageService $imageService
    ) {}
    public function index(Request $request)
    {
        $search = trim($request->search);
        $category = $request->category;
        $brand = $request->brand;
        $shoeType = $request->shoe_type;

        $products = Product::with(['category', 'brand', 'shoeType', 'variants'])
            ->where('is_active', true)

            ->when($search, function ($query) use ($search) {
                $search = strtolower($search);

                $query->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(product_name) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(product_description) LIKE ?', ["%{$search}%"])
                        ->orWhereHas('brand', fn($q) => $q->whereRaw('LOWER(brand_name) LIKE ?', ["%{$search}%"]))
                        ->orWhereHas('category', fn($q) => $q->whereRaw('LOWER(category_name) LIKE ?', ["%{$search}%"]))
                        ->orWhereHas('shoeType', fn($q) => $q->whereRaw('LOWER(shoe_type_name) LIKE ?', ["%{$search}%"]));
                });
            })

            ->when($category, fn($query) => $query->where('category_id', $category))
            ->when($brand, fn($query) => $query->where('brand_id', $brand))
            ->when($shoeType, fn($query) => $query->where('shoe_type_id', $shoeType))

            ->orderBy('product_name')
            ->paginate(5)
            ->withQueryString();

        return view('admin.products.index', [
            'products'        => $products,
            'categories'      => Category::where('is_active', true)->orderBy('category_name')->get(),
            'brands'          => Brand::where('is_active', true)->orderBy('brand_name')->get(),
            'shoeTypes'       => ShoeType::where('is_active', true)->orderBy('display_order')->get(),
            'search'          => $search,
            'totalProducts'   => Product::where('is_active', true)->count(),
            'totalVariants'   => ProductVariant::count(),
        ]);
    }

    public function store(Request $request)
    {
        $request->merge([
            'product_name' => ucwords(
                strtolower(
                    preg_replace('/\s+/', ' ', trim($request->product_name))
                )
            ),
        ]);

        $validated = $request->validate([
            'product_name'        => 'required|string|max:255',
            'product_description' => 'nullable|string',
            'category_id'         => 'required|exists:categories,category_id',
            'brand_id'            => 'required|exists:brands,brand_id',
            'shoe_type_id'        => 'required|exists:shoe_types,shoe_type_id',
            'images.*'            => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $exists = Product::whereRaw(
            'LOWER(product_name)=?',
            [strtolower($validated['product_name'])]
        )->exists();

        if ($exists) {
            return back()
                ->withErrors(['product_name' => 'This product already exists.'])
                ->withInput();
        }

        try {
            // Only pass the columns that actually belong to the products table
            $product = Product::create([
                'product_name'        => $validated['product_name'],
                'product_description' => $validated['product_description'] ?? null,
                'category_id'         => $validated['category_id'],
                'brand_id'            => $validated['brand_id'],
                'shoe_type_id'        => $validated['shoe_type_id'],
            ]);

            if ($request->hasFile('images')) {
                $this->imageService->storeMany($product, $request->file('images'));
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

    public function edit(Product $product)
    {
        return view('admin.products.edit', [
            'product'    => $product,
            'categories' => Category::where('is_active', true)->get(),
            'brands'     => Brand::where('is_active', true)->get(),
            'shoeTypes'  => ShoeType::where('is_active', true)
                                    ->orderBy('display_order')
                                    ->get(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $request->merge([
            'product_name' => ucwords(
                strtolower(
                    preg_replace('/\s+/', ' ', trim($request->product_name))
                )
            ),
        ]);

        $validated = $request->validate([
            'product_name'        => 'required|string|max:255',
            'product_description' => 'nullable|string',
            'category_id'         => 'required|exists:categories,category_id',
            'brand_id'            => 'required|exists:brands,brand_id',
            'shoe_type_id'        => 'required|exists:shoe_types,shoe_type_id',
            'images'              => 'nullable',
            'images.*'            => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'primary_image'       => 'nullable|integer',
        ]);

        $exists = Product::whereRaw(
            'LOWER(product_name)=?',
            [strtolower($validated['product_name'])]
        )
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
            $product->update($validated);

            return redirect()
                ->route('admin.products.index')
                ->with('success', 'Product updated successfully!');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to update product: ' . $e->getMessage());
        }
    }

    public function destroy(Product $product)
    {
        $product->update([
            'is_active' => false,
        ]);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product archived successfully!');
    }
}