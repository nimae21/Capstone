<?php

namespace App\Services;

use App\Jobs\RecordUserActivities;
use App\Models\Product;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class ActivityTrackingService
{
    public const WEIGHTS = [
        'view' => 1,
        'search' => 2,
        'add_to_cart' => 5,
    ];

    public function logView(User $user, Product $product): void
    {
        $this->log($user, $product, 'view');
    }

    public function logSearch(User $user, Product $product): void
    {
        $this->log($user, $product, 'search');
    }

    public function logSearchResults(User $user, iterable $products): void
    {
        $this->dispatch($user->id, collect($products)->pluck('product_id')->all(), 'search');
    }

    public function logAddToCart(User $user, Product $product): void
    {
        $this->log($user, $product, 'add_to_cart');
    }

    private function log(User $user, Product $product, string $type): void
    {
        $this->dispatch($user->id, [$product->product_id], $type);
    }

    private function dispatch(int $userId, array $productIds, string $type): void
    {
        $productIds = $this->normalizeProductIds($productIds);
        if ($productIds === []) {
            return;
        }

        RecordUserActivities::dispatch($userId, $productIds, $type, now()->toIso8601String());
    }

    public function recordNow(int $userId, array $productIds, string $type, string $occurredAt): void
    {
        if (! array_key_exists($type, self::WEIGHTS)) {
            throw new \InvalidArgumentException("Unsupported activity type [{$type}].");
        }

        $productIds = $this->normalizeProductIds($productIds);
        if ($productIds === []) {
            return;
        }

        $minutes = max(1, (int) config('activity_tracking.aggregation_minutes', 60));
        $occurred = CarbonImmutable::parse($occurredAt)->setTimezone(config('app.timezone'));
        $windowSeconds = $minutes * 60;
        $window = CarbonImmutable::createFromTimestamp(
            intdiv($occurred->getTimestamp(), $windowSeconds) * $windowSeconds,
            config('app.timezone'),
        );
        $now = now();
        $rows = array_map(fn (int $productId) => [
            'user_id' => $userId,
            'product_id' => $productId,
            'activity_type' => $type,
            'activity_window' => $window,
            'activity_count' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ], $productIds);

        $counter = DB::connection()->getDriverName() === 'pgsql'
            ? DB::raw('user_activities.activity_count + 1')
            : DB::raw('activity_count + 1');

        DB::table('user_activities')->upsert(
            $rows,
            ['user_id', 'product_id', 'activity_type', 'activity_window'],
            ['activity_count' => $counter, 'updated_at' => $now],
        );

        if ($type !== 'view') {
            app(RecommendationClient::class)->forgetForUser($userId);
        }
    }

    private function normalizeProductIds(array $productIds): array
    {
        return collect($productIds)
            ->filter(fn ($id) => is_int($id) || ctype_digit((string) $id))
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->take(100)
            ->values()
            ->all();
    }
}
