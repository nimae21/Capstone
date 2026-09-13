<?php

use App\Jobs\RecordUserActivities;
use App\Models\Product;
use App\Models\User;
use App\Models\UserActivity;
use App\Services\ActivityTrackingService;
use App\Services\RecommendationClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;

function activityProducts(int $count): array
{
    $category = DB::table('categories')->insertGetId(['category_name' => 'Activity'], 'category_id');
    $brand = DB::table('brands')->insertGetId(['brand_name' => 'Activity'], 'brand_id');
    $type = DB::table('shoe_types')->insertGetId(['shoe_type_name' => 'Activity'], 'shoe_type_id');

    return collect(range(1, $count))->map(fn ($number) => Product::create([
        'category_id' => $category,
        'brand_id' => $brand,
        'shoe_type_id' => $type,
        'product_name' => 'Activity Shoe '.$number,
        'is_active' => true,
    ]))->all();
}

beforeEach(function () {
    Cache::flush();
});

it('queues all displayed search results as one noncritical activity job', function () {
    Queue::fake();
    $user = User::factory()->create();
    $products = activityProducts(5);

    app(ActivityTrackingService::class)->logSearchResults($user, $products);

    Queue::assertPushed(RecordUserActivities::class, 1);
    Queue::assertPushed(fn (RecordUserActivities $job) => $job->userId === $user->id
        && $job->type === 'search'
        && $job->productIds === collect($products)->pluck('product_id')->all()
        && $job->afterCommit);
    expect(UserActivity::count())->toBe(0);
});

it('upserts one activity window in one database write and invalidates recommendations once', function () {
    $user = User::factory()->create();
    $products = activityProducts(5);
    $writes = [];
    DB::listen(function ($query) use (&$writes) {
        if (preg_match('/^(insert|update)/i', ltrim($query->sql))) {
            $writes[] = $query->sql;
        }
    });
    $recommendations = Mockery::mock(RecommendationClient::class);
    $recommendations->shouldReceive('forgetForUser')->once()->with($user->id);
    app()->instance(RecommendationClient::class, $recommendations);

    app(ActivityTrackingService::class)->recordNow(
        $user->id,
        collect($products)->pluck('product_id')->all(),
        'search',
        '2026-09-13T13:42:00+08:00',
    );

    expect(UserActivity::count())->toBe(5)
        ->and(UserActivity::pluck('activity_count')->all())->each->toBe(1)
        ->and($writes)->toHaveCount(1);
});

it('aggregates repeated signals in the configured window without changing their weights', function () {
    config(['activity_tracking.aggregation_minutes' => 60]);
    $user = User::factory()->create();
    [$product] = activityProducts(1);
    $service = app(ActivityTrackingService::class);

    $service->recordNow($user->id, [$product->product_id], 'add_to_cart', '2026-09-13T13:05:00+08:00');
    $service->recordNow($user->id, [$product->product_id], 'add_to_cart', '2026-09-13T13:55:00+08:00');
    $service->recordNow($user->id, [$product->product_id], 'add_to_cart', '2026-09-13T14:00:00+08:00');

    expect(UserActivity::orderBy('activity_window')->pluck('activity_count')->all())->toBe([2, 1])
        ->and(ActivityTrackingService::WEIGHTS)->toBe([
            'view' => 1,
            'search' => 2,
            'add_to_cart' => 5,
        ]);
});

it('keeps retention disabled unless an operator configures it', function () {
    config(['activity_tracking.retention_days' => 0]);
    $user = User::factory()->create();
    [$product] = activityProducts(1);
    UserActivity::create([
        'user_id' => $user->id,
        'product_id' => $product->product_id,
        'activity_type' => 'view',
        'created_at' => now()->subYears(2),
        'updated_at' => now()->subYears(2),
    ]);

    $this->artisan('activities:prune')->assertSuccessful();
    expect(UserActivity::count())->toBe(1);
});

it('preserves aggregate strength when the schema migration is rolled back', function () {
    $user = User::factory()->create();
    [$product] = activityProducts(1);
    $service = app(ActivityTrackingService::class);
    $service->recordNow($user->id, [$product->product_id], 'view', '2026-09-13T13:05:00+08:00');
    $service->recordNow($user->id, [$product->product_id], 'view', '2026-09-13T13:10:00+08:00');
    $migration = require database_path('migrations/2026_09_13_000003_add_user_activity_aggregation.php');

    try {
        $migration->down();

        expect(Schema::hasColumn('user_activities', 'activity_count'))->toBeFalse()
            ->and(UserActivity::count())->toBe(2);
    } finally {
        $migration->up();
    }
});
