<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Stock;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecommendationClient
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.recommendation.url', 'http://127.0.0.1:5000');
    }

    /**
     * Fetch personalized product recommendations for a user.
     * Returns an empty collection on any failure (timeout, service
     * down, no data) rather than throwing — a broken recommendation
     * call should never break the page it's embedded in.
     */
    public function forUser(int $userId, int $limit = 8): Collection
    {
        $limit = max(1, min(20, $limit));
        $productIds = Cache::remember(
            $this->cacheKey($userId, $limit),
            now()->addMinutes((int) config('services.recommendation.cache_minutes', 10)),
            fn () => $this->fetchProductIds($userId, $limit),
        );

        return $this->loadCards($productIds);
    }

    public function cachedForUser(int $userId, int $limit = 8): Collection
    {
        $limit = max(1, min(20, $limit));
        $productIds = Cache::get($this->cacheKey($userId, $limit));

        return is_array($productIds) ? $this->loadCards($productIds) : collect();
    }

    public function isCachedForUser(int $userId, int $limit = 8): bool
    {
        return Cache::has($this->cacheKey($userId, max(1, min(20, $limit))));
    }

    public function forgetForUser(int $userId): void
    {
        $versionKey = "recommendations.user.{$userId}.version";
        Cache::forever($versionKey, ((int) Cache::get($versionKey, 1)) + 1);
    }

    private function fetchProductIds(int $userId, int $limit): array
    {
        try {
            $key = (string) config('services.recommendation.key');
            if ($key === '') {
                Log::warning('Recommendation service key is not configured.');

                return [];
            }

            $response = Http::connectTimeout((int) config('services.recommendation.connect_timeout', 1))
                ->timeout((int) config('services.recommendation.timeout', 2))
                ->withHeaders(['X-Recommendation-Key' => $key])
                ->get("{$this->baseUrl}/recommendations/{$userId}", [
                    'limit' => $limit,
                ]);

            if ($response->failed()) {
                Log::warning("Recommendation service returned an error for user {$userId}: ".$response->status());

                return [];
            }

            $productIds = $response->json('product_ids', []);

            return collect($productIds)->filter(fn ($id) => is_int($id) || ctype_digit((string) $id))
                ->map(fn ($id) => (int) $id)->unique()->take($limit)->values()->all();

        } catch (\Throwable $e) {
            // Connection refused, timeout, DNS failure, etc. — the
            // Python service being unreachable should degrade silently.
            Log::warning('Recommendation service unreachable.', ['exception' => get_class($e)]);

            return [];
        }
    }

    private function loadCards(array $productIds): Collection
    {
        if ($productIds === []) {
            return collect();
        }

        return Product::query()
            ->select('products.product_id', 'products.product_name', 'products.brand_id',
                'products.is_active', 'products.new_arrival_until')
            ->addSelect(['display_price' => Stock::query()
                ->select('stocks.price')
                ->where('stocks.product_variant_id', ProductVariant::query()
                    ->select('product_variants.product_variant_id')
                    ->whereColumn('product_variants.product_id', 'products.product_id')
                    ->orderBy('product_variants.product_variant_id')
                    ->limit(1))
                ->orderByDesc('stocks.deliver_date')
                ->limit(1)])
            ->with([
                'brand:brand_id,brand_name',
                'primaryImage:image_id,product_id,image_path,thumbnail_path,medium_path,large_path,image_width,image_height,thumbnail_width,thumbnail_height,medium_width,medium_height,large_width,large_height,is_primary',
            ])
            ->whereIn('products.product_id', $productIds)
            ->where('products.is_active', true)
            ->get()
            ->sortBy(fn ($product) => array_search($product->product_id, $productIds, true))
            ->values();
    }

    private function cacheKey(int $userId, int $limit): string
    {
        $version = (int) Cache::get("recommendations.user.{$userId}.version", 1);

        return "recommendations.user.{$userId}.v{$version}.{$limit}";
    }
}
