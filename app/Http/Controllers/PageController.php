<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ShoeType;
use App\Services\ActivityTrackingService;
use App\Services\RecommendationClient;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class PageController extends Controller
{
    public function __construct(
        protected ActivityTrackingService $activityTracker
    ) {}

    public function home()
    {
        $recommendations = $this->recommendationsForCurrentUser();

        return view('pages.home', compact('recommendations'));
    }

    /**
     * Reusable, filterable product query for category pages.
     */
    private function getProductsByCategory(?int $categoryId, Request $request)
    {
        $filters = $request->validate([
            'brand' => ['nullable', 'integer', 'exists:brands,brand_id'],
            'shoe_type' => ['nullable', 'integer', 'exists:shoe_types,shoe_type_id'],
            'sort' => ['nullable', 'in:price-low-high,price-high-low'],
            'page' => ['nullable', 'integer', 'min:1', 'max:1000'],
        ]);

        $query = Product::with([
            'brand:brand_id,brand_name',
            'shoeType:shoe_type_id,shoe_type_name',
            // Cards need only the first image. This limit is applied per product.
            'images' => fn ($images) => $images
                ->select('image_id', 'product_id', 'image_path', 'display_order')
                ->orderBy('image_id')->limit(1),
        ])
            ->where('is_active', true)
            ->withDisplayPrice();

        if ($categoryId === null) {
            $query->newArrivals();
        } else {
            $query->where('category_id', $categoryId);
        }

        if (isset($filters['brand'])) {
            $query->where('brand_id', $filters['brand']);
        }

        if (isset($filters['shoe_type'])) {
            $query->where('shoe_type_id', $filters['shoe_type']);
        }

        if (isset($filters['sort'])) {
            $direction = $filters['sort'] === 'price-low-high' ? 'asc' : 'desc';
            $query->orderBy('display_price', $direction);
        } elseif ($categoryId === null) {
            $query->orderByDesc('products.created_at')->orderByDesc('products.product_id');
        } else {
            $query->orderBy('product_name');
        }

        return $query->paginate(9)->withQueryString();
    }

    /**
     * Brands/shoe types relevant to filter dropdowns, scoped to what's
     * actually active — avoids showing filter options with zero products.
     */
    private function filterOptions(): array
    {
        return Cache::remember('catalog.filter-options.v1', now()->addMinutes(10), fn () => [
            'brands' => Brand::where('is_active', true)->orderBy('brand_name')
                ->get(['brand_id', 'brand_name']),
            'shoeTypes' => ShoeType::where('is_active', true)->orderBy('display_order')
                ->get(['shoe_type_id', 'shoe_type_name']),
        ]);
    }

    private function recommendationsForCurrentUser(): Collection
    {
        return auth()->check()
            ? app(RecommendationClient::class)->forUser(auth()->id())
            : collect();
    }

    public function men(Request $request)
    {
        return view('pages.men', array_merge([
            'products' => $this->getProductsByCategory(1, $request),
            'recommendations' => $this->recommendationsForCurrentUser(),
        ], $this->filterOptions()));
    }

    public function women(Request $request)
    {
        return view('pages.women', array_merge([
            'products' => $this->getProductsByCategory(2, $request),
            'recommendations' => $this->recommendationsForCurrentUser(),
        ], $this->filterOptions()));
    }

    public function kids(Request $request)
    {
        return view('pages.kids', array_merge([
            'products' => $this->getProductsByCategory(5, $request),
            'recommendations' => $this->recommendationsForCurrentUser(),
        ], $this->filterOptions()));
    }

    public function new(Request $request)
    {
        return view('pages.new', array_merge([
            'products' => $this->getProductsByCategory(null, $request),
            'recommendations' => $this->recommendationsForCurrentUser(),
        ], $this->filterOptions()));
    }

    public function showProduct($id)
    {
        $product = Product::with([
            'images', 'category', 'brand',
            'variants' => fn ($query) => $query->where('is_active', true)
                ->with(['stocks' => fn ($stocks) => $stocks->where('is_archived', false)]),
        ])->where('is_active', true)->findOrFail($id);

        if (auth()->check()) {
            $this->activityTracker->logView(auth()->user(), $product);
        }

        foreach ($product->variants as $variant) {
            $variant->available_stock = $variant->stocks->sum('remaining_quantity');
            $latestStock = $variant->stocks->sortByDesc('deliver_date')->first();
            $variant->current_price = $latestStock?->price ?? 0;
        }

        $recommendations = auth()->check()
           ? app(RecommendationClient::class)->forUser(auth()->id())->reject(fn ($p) => $p->product_id === $product->product_id)
           : collect();

        return view('product.show', compact('product', 'recommendations'));
    }

    public function searchSuggestions(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1', 'max:1000'],
        ]);
        $query = trim($validated['q'] ?? '');

        if (mb_strlen($query) < 2) {
            return response()->json(['products' => [], 'next_page' => null]);
        }

        $term = mb_strtolower($query);
        // Treat SQL wildcard characters as literal search text.
        $pattern = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $term);
        $products = Product::select('product_id', 'product_name')
            ->where('is_active', true)
            ->whereRaw("LOWER(product_name) LIKE ? ESCAPE '!'", ['%'.$pattern.'%'])
            ->orderByRaw("CASE WHEN LOWER(product_name) = ? THEN 0 WHEN LOWER(product_name) LIKE ? ESCAPE '!' THEN 1 ELSE 2 END", [$term, $pattern.'%'])
            ->orderBy('product_name')
            ->orderBy('product_id')
            // No total-count query; fetch just enough to detect another batch.
            ->simplePaginate(3, ['*'], 'page', $validated['page'] ?? 1);

        // Fetch images only for the three selected products, after matching/sorting.
        $products->getCollection()->load([
            'images' => fn ($images) => $images->reorder()
                ->orderByDesc('is_primary')->orderBy('display_order')->orderBy('image_id')
                ->select('image_id', 'product_id', 'image_path')->limit(1),
        ]);

        return response()->json([
            'products' => $products->getCollection()->map(fn (Product $product) => [
                'name' => $product->product_name,
                'image' => $product->images->first()?->image_url,
                'url' => route('product.show', $product->product_id),
            ])->values(),
            'next_page' => $products->hasMorePages() ? $products->currentPage() + 1 : null,
        ]);
    }

    public function search(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1', 'max:1000'],
        ]);
        $query = trim($validated['q'] ?? '');

        $products = collect();

        if ($query !== '') {
            $term = mb_strtolower($query);
            $pattern = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $term);
            $products = Product::with(['variants.stocks', 'images', 'brand', 'category', 'shoeType'])->withDisplayPrice()
                ->where('is_active', true)
                ->whereRaw("LOWER(product_name) LIKE ? ESCAPE '!'", ['%'.$pattern.'%'])
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
            'recommendations' => $this->recommendationsForCurrentUser(),
        ]);
    }
}
