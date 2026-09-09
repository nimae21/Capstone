<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Storage;
use App\Models\ShoeType;
use App\Services\ActivityTrackingService;
use App\Services\RecommendationClient;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;


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
    $query = Product::with(['variants.stocks', 'images'])
        ->where('is_active', true)
        ->withDisplayPrice();

    if ($categoryId === null) {
        $query->newArrivals();
    } else {
        $query->where('category_id', $categoryId);
    }

    if ($request->filled('brand')) {
        $query->where('brand_id', $request->brand);
    }

    if ($request->filled('shoe_type')) {
        $query->where('shoe_type_id', $request->shoe_type);
    }

    if ($request->filled('sort') && in_array($request->sort, ['price-low-high', 'price-high-low'])) {
        $direction = $request->sort === 'price-low-high' ? 'asc' : 'desc';
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
        return [
            'brands' => Brand::where('is_active', true)->orderBy('brand_name')->get(),
            'shoeTypes' => ShoeType::where('is_active', true)->orderBy('display_order')->get(),
        ];
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
            'images', 'category', 'brand', 'variants.stocks',
        ])->findOrFail($id);

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
        $validated = $request->validate(['q' => ['nullable', 'string', 'max:100']]);
        $query = trim($validated['q'] ?? '');

        if (mb_strlen($query) < 2) {
            return response()->json(['products' => []]);
        }

        // Select only the preview image instead of fetching every product image.
        $previewImage = ProductImage::select('image_path')
            ->whereColumn('product_images.product_id', 'products.product_id')
            ->orderByDesc('is_primary')
            ->orderBy('display_order')
            ->orderBy('image_id')
            ->limit(1);

        $products = Product::select('product_id', 'product_name')
            ->addSelect(['preview_image_path' => $previewImage])
            ->where('is_active', true)
            ->whereRaw('LOWER(product_name) LIKE ?', ['%'.mb_strtolower($query).'%'])
            ->orderBy('product_name')
            ->orderBy('product_id')
            ->limit(5)
            ->get()
            ->map(function (Product $product) {
                return [
                    'name' => $product->product_name,
                    'image' => $product->preview_image_path !== null
                        ? Storage::disk('supabase')->url($product->preview_image_path)
                        : null,
                    'url' => route('product.show', $product->product_id),
                ];
            });

        return response()->json(['products' => $products]);
    }

    public function search(Request $request)
    {
        $query = trim((string) $request->input('q'));

        $products = collect();

        if ($query !== '') {
            $products = Product::with(['variants.stocks', 'images', 'brand', 'category', 'shoeType'])->withDisplayPrice()
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
            'recommendations' => $this->recommendationsForCurrentUser(),
        ]);
    }
}
