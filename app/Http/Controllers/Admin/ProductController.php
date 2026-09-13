<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShoeType;
use App\Services\ApprovalService;
use App\Services\ProductImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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

        $products = Product::query()
            ->select(
                'products.product_id',
                'products.category_id',
                'products.brand_id',
                'products.shoe_type_id',
                'products.product_name',
                'products.product_description',
                'products.is_active',
                'products.new_arrival_until',
                'categories.category_name',
                'brands.brand_name',
                'shoe_types.shoe_type_name',
            )
            ->join('categories', 'categories.category_id', '=', 'products.category_id')
            ->join('brands', 'brands.brand_id', '=', 'products.brand_id')
            ->join('shoe_types', 'shoe_types.shoe_type_id', '=', 'products.shoe_type_id')
            ->with([
                'variants' => fn ($query) => $query
                    ->select('product_variant_id', 'product_id', 'size', 'color')
                    ->withSum('stocks as available_stock', 'remaining_quantity')
                    ->orderBy('color')
                    ->orderByRaw('CAST(size AS DECIMAL(4,1))'),
            ])
            ->where('products.is_active', true)

            ->when($search, function ($query) use ($search) {
                $search = strtolower($search);

                $query->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(products.product_name) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(products.product_description) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(brands.brand_name) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(categories.category_name) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(shoe_types.shoe_type_name) LIKE ?', ["%{$search}%"]);
                });
            })

            ->when($category, fn ($query) => $query->where('products.category_id', $category))
            ->when($brand, fn ($query) => $query->where('products.brand_id', $brand))
            ->when($shoeType, fn ($query) => $query->where('products.shoe_type_id', $shoeType))

            ->orderBy('products.product_name')
            ->orderBy('products.product_id')
            ->paginate(5)
            ->withQueryString();

        $counts = DB::query()
            ->selectSub(Product::query()->where('is_active', true)->selectRaw('COUNT(*)'), 'total_products')
            ->selectSub(ProductVariant::query()->selectRaw('COUNT(*)'), 'total_variants')
            ->first();

        return view('admin.products.index', [
            'products' => $products,
            'categories' => DB::table('categories')->where('is_active', true)
                ->select('category_id', 'category_name')->orderBy('category_name')->get(),
            'brands' => DB::table('brands')->where('is_active', true)
                ->select('brand_id', 'brand_name')->orderBy('brand_name')->get(),
            'shoeTypes' => DB::table('shoe_types')->where('is_active', true)
                ->select('shoe_type_id', 'shoe_type_name')->orderBy('display_order')->get(),
            'search' => $search,
            'totalProducts' => (int) $counts->total_products,
            'totalVariants' => (int) $counts->total_variants,
        ]);
    }

    public function store(Request $request)
    {
        return app(ApprovalService::class)->submit($request, 'product');
    }

    public function edit(Product $product)
    {
        return view('admin.products.edit', [
            'product' => $product,
            'categories' => Category::where('is_active', true)->get(),
            'brands' => Brand::where('is_active', true)->get(),
            'shoeTypes' => ShoeType::where('is_active', true)
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
            'product_name' => 'required|string|max:255',
            'product_description' => 'nullable|string',
            'new_arrival_until' => 'sometimes|nullable|date_format:Y-m-d',
            'category_id' => 'required|exists:categories,category_id',
            'brand_id' => 'required|exists:brands,brand_id',
            'shoe_type_id' => 'required|exists:shoe_types,shoe_type_id',
            'images' => 'nullable',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'primary_image' => 'nullable|integer',
        ]);

        if (array_key_exists('new_arrival_until', $validated)) {
            $validated['new_arrival_until'] = $validated['new_arrival_until']
                ? Carbon::parse($validated['new_arrival_until'])->endOfDay()
                : null;
        }

        $exists = Product::whereRaw(
            'LOWER(product_name)=?',
            [strtolower($validated['product_name'])]
        )
            ->where('product_id', '!=', $product->product_id)
            ->exists();

        if ($exists) {
            return back()
                ->withErrors([
                    'product_name' => 'This product already exists.',
                ])
                ->withInput();
        }

        try {
            $product->update($validated);

            return redirect()
                ->route('admin.products.index')
                ->with('success', 'Product updated successfully!');

        } catch (\Exception $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', 'The product could not be updated. Please try again.');
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
