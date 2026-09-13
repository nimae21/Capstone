<?php

namespace App\Http\Controllers;

use App\Models\Brand;
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

    private function getProductsByCategory(?int $categoryId, Request $request)
    {
        $filters = $request->validate([
            'brand' => ['nullable', 'integer', 'exists:brands,brand_id'],
            'shoe_type' => ['nullable', 'integer', 'exists:shoe_types,shoe_type_id'],
            'sort' => ['nullable', 'in:price-low-high,price-high-low'],
            'page' => ['nullable', 'integer', 'min:1', 'max:1000'],
        ]);

        $query = Product::query()
            ->forStorefrontCards()
            ->with([
                'brand:brand_id,brand_name',
                'shoeType:shoe_type_id,shoe_type_name',
                'images' => fn ($images) => $images
                    ->select('image_id', 'product_id', 'image_path', 'is_primary', 'display_order')
                    ->reorder()->orderByDesc('is_primary')->orderBy('display_order')->orderBy('image_id')->limit(1),
            ])
            ->where('products.is_active', true);

        if ($categoryId === null) {
            $query->newArrivals();
        } else {
            $query->where('products.category_id', $categoryId);
        }

        if (isset($filters['brand'])) {
            $query->where('products.brand_id', $filters['brand']);
        }

        if (isset($filters['shoe_type'])) {
            $query->where('products.shoe_type_id', $filters['shoe_type']);
        }

        if (isset($filters['sort'])) {
            $query->orderBy('display_price', $filters['sort'] === 'price-low-high' ? 'asc' : 'desc');
        } elseif ($categoryId === null) {
            $query->orderByDesc('products.created_at')->orderByDesc('products.product_id');
        } else {
            $query->orderBy('products.product_name')->orderBy('products.product_id');
        }

        return $query->paginate(9)->withQueryString();
    }

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
            ? app(RecommendationClient::class)->cachedForUser(auth()->id())
            : collect();
    }

    public function recommendations(Request $request)
    {
        $validated = $request->validate([
            'exclude_product_id' => ['nullable', 'integer', 'min:1'],
        ]);
        $recommendations = app(RecommendationClient::class)->forUser(auth()->id());

        if (isset($validated['exclude_product_id'])) {
            $recommendations = $recommendations->reject(
                fn (Product $product) => $product->product_id === (int) $validated['exclude_product_id'],
            );
        }

        return response()->view('partials.recommendations', [
            'recommendations' => $recommendations,
        ]);
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
            'images:image_id,product_id,color,image_path,is_primary,display_order',
            'category:category_id,category_name',
            'brand:brand_id,brand_name',
            'variants' => fn ($query) => $query
                ->select('product_variant_id', 'product_id', 'size', 'color', 'is_active')
                ->where('is_active', true)
                ->withStorefrontStock()
                ->orderBy('color')->orderBy('size')->orderBy('product_variant_id'),
        ])->where('is_active', true)->findOrFail($id);

        if (auth()->check()) {
            $this->activityTracker->logView(auth()->user(), $product);
        }

        $recommendations = auth()->check()
            ? app(RecommendationClient::class)->cachedForUser(auth()->id())
                ->reject(fn ($p) => $p->product_id === $product->product_id)
            : collect();

        $recommendationExcludeProductId = $product->product_id;

        return view('product.show', compact('product', 'recommendations', 'recommendationExcludeProductId'));
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
        $pattern = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $term);
        $products = Product::select('product_id', 'product_name')
            ->where('is_active', true)
            ->whereRaw("LOWER(product_name) LIKE ? ESCAPE '!'", ['%'.$pattern.'%'])
            ->orderByRaw("CASE WHEN LOWER(product_name) = ? THEN 0 WHEN LOWER(product_name) LIKE ? ESCAPE '!' THEN 1 ELSE 2 END", [$term, $pattern.'%'])
            ->orderBy('product_name')->orderBy('product_id')
            ->simplePaginate(3, ['*'], 'page', $validated['page'] ?? 1);

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
            $products = Product::query()
                ->forStorefrontCards()
                ->with([
                    'brand:brand_id,brand_name',
                    'category:category_id,category_name',
                    'shoeType:shoe_type_id,shoe_type_name',
                    'images' => fn ($images) => $images
                        ->select('image_id', 'product_id', 'image_path', 'is_primary', 'display_order')
                        ->reorder()->orderByDesc('is_primary')->orderBy('display_order')->orderBy('image_id')->limit(1),
                ])
                ->where('products.is_active', true)
                ->whereRaw("LOWER(products.product_name) LIKE ? ESCAPE '!'", ['%'.$pattern.'%'])
                ->orderBy('products.product_name')->orderBy('products.product_id')
                ->paginate(12)->withQueryString();

            if (auth()->check()) {
                $this->activityTracker->logSearchResults(auth()->user(), $products->take(5));
            }
        }

        return view('pages.search', [
            'products' => $products,
            'query' => $query,
            'recommendations' => $this->recommendationsForCurrentUser(),
        ]);
    }
}
