<?php

namespace App\Services;

use App\Jobs\RecordUserActivities;
use App\Models\BackgroundOperation;
use App\Models\Product;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

        $occurredAt = now()->toIso8601String();
        $operationKey = 'activity:'.Str::uuid();
        $now = now();

        DB::table('background_operations')->insertOrIgnore([
            'operation_key' => $operationKey,
            'type' => 'user_activity',
            'payload' => json_encode([
                'user_id' => $userId,
                'product_ids' => $productIds,
                'activity_type' => $type,
                'occurred_at' => $occurredAt,
            ], JSON_THROW_ON_ERROR),
            'available_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        app(ReliableJobDispatcher::class)->dispatch(new RecordUserActivities(
            $userId,
            $productIds,
            $type,
            $occurredAt,
            $operationKey,
        ));
    }

    public function processOperation(string $operationKey): void
    {
        $invalidateUser = DB::transaction(function () use ($operationKey): ?int {
            $operation = BackgroundOperation::where('operation_key', $operationKey)
                ->lockForUpdate()->first();

            if (! $operation || $operation->processed_at || $operation->type !== 'user_activity') {
                return null;
            }

            $payload = $operation->payload;
            $this->recordRows(
                (int) $payload['user_id'],
                (array) $payload['product_ids'],
                (string) $payload['activity_type'],
                (string) $payload['occurred_at'],
            );
            $operation->update([
                'attempts' => $operation->attempts + 1,
                'processed_at' => now(),
                'last_error' => null,
            ]);

            return $payload['activity_type'] === 'view' ? null : (int) $payload['user_id'];
        });

        if ($invalidateUser) {
            $this->invalidateRecommendations($invalidateUser);
        }
    }

    public function recordNow(int $userId, array $productIds, string $type, string $occurredAt): void
    {
        $this->recordRows($userId, $productIds, $type, $occurredAt);

        if ($type !== 'view') {
            $this->invalidateRecommendations($userId);
        }
    }

    private function recordRows(int $userId, array $productIds, string $type, string $occurredAt): void
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
    }

    private function invalidateRecommendations(int $userId): void
    {
        try {
            app(RecommendationClient::class)->forgetForUser($userId);
        } catch (\Throwable $exception) {
            report($exception);
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
