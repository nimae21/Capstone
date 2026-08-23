<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ShoeType;
use App\Services\ActivityTrackingService;
use App\Services\RecommendationClient;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function __construct(
        protected ActivityTrackingService $activityTracker
    ) {}

    public function home()
    {
        $recommendations = auth()->check()
        ? app(RecommendationClient::class)->forUser(auth()->id())
        : collect();

        return view('pages.home', compact('recommendations'));
    }

    /**
     * Reusable, filterable product query for category pages.
     */
    private function getProductsByCategory(int $categoryId, Request $request)
    {
        $query = Product::with(['variants.stocks', 'images'])
            ->where('is_active', true)
            ->where('category_id', $categoryId);

        if ($request->filled('brand')) {
            $query->where('brand_id', $request->brand);
        }

        if ($request->filled('shoe_type')) {
            $query->where('shoe_type_id', $request->shoe_type);
        }

        if ($request->filled('sort') && $request->sort === 'price-low-high') {
            // Price lives on stocks, not products — sort client-side already
            // handles this in JS; server-side price sort would need a join.
            // Left as-is since existing JS sort already covers it.
        }

        return $query->orderBy('product_name')->paginate(9)->withQueryString();
    }

    /**
     * Brands/shoe types relevant to filter dropdowns, scoped to what's
     * actually active — avoids showing filter options with zero products.
     */
    private function filterOptions(): array
    {
        return [
            'brands' => Brand::where('is_active', true)->orderBy('brand_name')->get(),
            'shoeTypes' => ShoeType::where('is_active', true)->orderBy('display_order')->get(),
        ];
    }

    public function men(Request $request)
    {
        return view('pages.men', array_merge([
            'products' => $this->getProductsByCategory(1, $request),
        ], $this->filterOptions()));
    }

    public function women(Request $request)
    {
        return view('pages.women', array_merge([
            'products' => $this->getProductsByCategory(2, $request),
        ], $this->filterOptions()));
    }

    public function kids(Request $request)
    {
        return view('pages.kids', array_merge([
            'products' => $this->getProductsByCategory(5, $request),
        ], $this->filterOptions()));
    }

    public function sale(Request $request)
    {
        return view('pages.sale', array_merge([
            'products' => $this->getProductsByCategory(7, $request),
        ], $this->filterOptions()));
    }

    public function new(Request $request)
    {
        return view('pages.new', array_merge([
            'products' => $this->getProductsByCategory(7, $request),
        ], $this->filterOptions()));
    }

    public function showProduct($id)
    {
        $product = Product::with([
            'images', 'category', 'brand', 'variants.stocks',
        ])->findOrFail($id);

        if (auth()->check()) {
            $this->activityTracker->logView(auth()->user(), $product);
        }

        foreach ($product->variants as $variant) {
            $variant->available_stock = $variant->stocks->sum('remaining_quantity');
            $latestStock = $variant->stocks()->latest('deliver_date')->first();
            $variant->current_price = $latestStock?->price ?? 0;
        }

        $recommendations = auth()->check()
           ? app(RecommendationClient::class)->forUser(auth()->id())->reject(fn ($p) => $p->product_id === $product->product_id)
           : collect();

        return view('product.show', compact('product', 'recommendations'));
    }

    public function search(Request $request)
    {
        $query = trim((string) $request->input('q'));

        $products = collect();

        if ($query !== '') {
            $products = Product::with(['variants.stocks', 'images', 'brand', 'category'])
                ->where('is_active', true)
                ->whereRaw('LOWER(product_name) LIKE ?', ['%'.strtolower($query).'%'])
                ->orderBy('product_name')
                ->paginate(12)
                ->withQueryString();

            // Log a 'search' activity for the top results shown — this is
            // the signal used later by the recommendation engine, treating
            // "appeared in a matching search" as a moderate interest signal.
            if (auth()->check()) {
                foreach ($products->take(5) as $product) {
                    $this->activityTracker->logSearch(auth()->user(), $product);
                }
            }
        }

        return view('pages.search', [
            'products' => $products,
            'query' => $query,
        ]);
    }
}
