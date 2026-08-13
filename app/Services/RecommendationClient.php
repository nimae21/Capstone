<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

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
        try {
            $response = Http::timeout(2)
                ->get("{$this->baseUrl}/recommendations/{$userId}", [
                    'limit' => $limit,
                ]);

            if ($response->failed()) {
                Log::warning("Recommendation service returned an error for user {$userId}: " . $response->status());
                return collect();
            }

            $productIds = $response->json('product_ids', []);

            if (empty($productIds)) {
                return collect();
            }

            // Preserve the order the service returned (its ranking),
            // rather than whatever order the DB happens to return rows in.
            $products = Product::with(['images', 'brand', 'variants.stocks'])
                ->whereIn('product_id', $productIds)
                ->where('is_active', true)
                ->get()
                ->sortBy(fn ($product) => array_search($product->product_id, $productIds))
                ->values();

            foreach ($products as $product) {
                $variant = $product->variants->first();
                $product->display_price = $variant?->stocks->sortByDesc('deliver_date')->first()?->price ?? 0;
            }

            return $products;

        } catch (\Throwable $e) {
            // Connection refused, timeout, DNS failure, etc. — the
            // Python service being unreachable should degrade silently.
            Log::warning("Recommendation service unreachable: " . $e->getMessage());
            return collect();
        }
    }
}