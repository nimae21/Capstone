<?php

use App\Jobs\DeliverMobilePush;
use App\Jobs\ProcessAdminAlert;
use App\Jobs\QueueHeartbeat;
use App\Jobs\RecordUserActivities;
use App\Models\BackgroundOperation;
use App\Models\Brand;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShoeType;
use App\Models\Stock;
use App\Models\User;
use App\Models\UserActivity;
use App\Services\ActivityTrackingService;
use App\Services\QueueMonitor;
use App\Services\ReliableJobDispatcher;
use App\Services\SuperAdminNotifier;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

it('defines isolated Redis connections without requiring Redis in tests', function () {
    expect(config('cache.default'))->toBe('array')
        ->and(config('session.driver'))->toBe('array')
        ->and(config('queue.default'))->toBe('sync')
        ->and(config('cache.stores.redis_rate_limit.connection'))->toBe('rate_limit')
        ->and(config('cache.stores.redis.lock_connection'))->toBe('lock')
        ->and(config('database.redis'))->toHaveKeys(['default', 'cache', 'session', 'rate_limit', 'lock'])
        ->and(config('queue.connections.redis.after_commit'))->toBeTrue()
        ->and(config('queue.connections.redis.block_for'))->toBe(5);
});

it('keeps encrypted secure browser sessions compatible with authentication', function () {
    $this->withoutVite();
    config([
        'session.encrypt' => true,
        'session.secure' => true,
        'session.http_only' => true,
        'session.same_site' => 'lax',
    ]);
    $user = User::factory()->create([
        'role' => 'user',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->post('/login', ['email' => $user->email, 'password' => 'password'])
        ->assertRedirect('/home');

    $this->assertAuthenticatedAs($user);
});

it('dispatches durable observer work after commit and discards it on rollback', function () {
    Queue::fake();
    User::factory()->create(['role' => 'super_admin', 'is_active' => true]);
    $customer = User::factory()->create(['role' => 'user']);

    DB::beginTransaction();
    Order::create([
        'user_id' => $customer->id,
        'sale_type' => 'online',
        'status' => 'pending',
        'total_amount' => 100,
        'full_name' => 'Customer',
        'phone_number' => '09170000000',
        'street' => 'Street',
        'barangay' => 'Barangay',
        'city' => 'City',
        'province' => 'Province',
        'postal_code' => '1000',
    ]);
    Queue::assertNotPushed(ProcessAdminAlert::class);
    DB::rollBack();

    expect(BackgroundOperation::count())->toBe(0);
    Queue::assertNotPushed(ProcessAdminAlert::class);

    DB::beginTransaction();
    Order::create([
        'user_id' => $customer->id,
        'sale_type' => 'online',
        'status' => 'pending',
        'total_amount' => 100,
        'full_name' => 'Customer',
        'phone_number' => '09170000000',
        'street' => 'Street',
        'barangay' => 'Barangay',
        'city' => 'City',
        'province' => 'Province',
        'postal_code' => '1000',
    ]);
    Queue::assertNotPushed(ProcessAdminAlert::class);
    DB::commit();

    Queue::assertPushed(ProcessAdminAlert::class, 1);
});

it('records one durable activity operation exactly once across job retries', function () {
    Queue::fake();
    $user = User::factory()->create();
    $category = DB::table('categories')->insertGetId(['category_name' => 'Queue'], 'category_id');
    $brand = Brand::create(['brand_name' => 'Queue Brand']);
    $type = ShoeType::create(['shoe_type_name' => 'Queue Type']);
    $product = Product::create([
        'category_id' => $category,
        'brand_id' => $brand->brand_id,
        'shoe_type_id' => $type->shoe_type_id,
        'product_name' => 'Queued Product',
        'is_active' => true,
    ]);

    app(ActivityTrackingService::class)->logSearchResults($user, [$product]);
    $operation = BackgroundOperation::where('type', 'user_activity')->sole();
    $job = new RecordUserActivities(
        $user->id,
        [$product->product_id],
        'search',
        now()->toIso8601String(),
        $operation->operation_key,
    );

    app()->call([$job, 'handle']);
    app()->call([$job, 'handle']);

    expect(UserActivity::count())->toBe(1)
        ->and(UserActivity::first()->activity_count)->toBe(1)
        ->and($operation->fresh()->processed_at)->not->toBeNull()
        ->and($job->tries)->toBe(5)
        ->and($job->timeout)->toBe(30)
        ->and($job->backoff())->toBe([10, 30, 60, 300]);
});

it('materializes duplicate observer work only once', function () {
    Queue::fake();
    config(['mobile_push.enabled' => false]);
    User::factory()->create(['role' => 'super_admin', 'is_active' => true]);

    app(SuperAdminNotifier::class)->alert(
        'security_alert',
        'Stable alert',
        'Only one notification should be stored.',
        '/tabs/logs',
        [],
        'test-alert:stable',
    );
    $operation = BackgroundOperation::where('operation_key', 'test-alert:stable')->sole();
    $job = new ProcessAdminAlert($operation->operation_key);

    app()->call([$job, 'handle']);
    app()->call([$job, 'handle']);

    $this->assertDatabaseCount('notifications', 1);
    expect($operation->fresh()->processed_at)->not->toBeNull();
});

it('keeps a customer product request successful when queue dispatch fails', function () {
    $this->withoutVite();
    $user = User::factory()->create(['role' => 'user', 'is_active' => true]);
    $category = DB::table('categories')->insertGetId(['category_name' => 'Request'], 'category_id');
    $brand = Brand::create(['brand_name' => 'Request Brand']);
    $type = ShoeType::create(['shoe_type_name' => 'Request Type']);
    $product = Product::create([
        'category_id' => $category,
        'brand_id' => $brand->brand_id,
        'shoe_type_id' => $type->shoe_type_id,
        'product_name' => 'Request Product',
        'is_active' => true,
    ]);
    $dispatcher = Mockery::mock(Dispatcher::class);
    $dispatcher->shouldReceive('dispatch')->andThrow(new RuntimeException('queue unavailable'));
    app()->instance(Dispatcher::class, $dispatcher);

    $this->actingAs($user)->get(route('product.show', $product))->assertOk();

    expect(BackgroundOperation::where('type', 'user_activity')->count())->toBe(1);
});

it('reconciles each supported due operation with valid job arguments', function () {
    Queue::fake();
    $now = now();
    BackgroundOperation::create([
        'operation_key' => 'activity:reconcile-test',
        'type' => 'user_activity',
        'payload' => [
            'user_id' => 42,
            'product_ids' => [10, 11],
            'activity_type' => 'search',
            'occurred_at' => $now->toIso8601String(),
        ],
        'available_at' => $now,
    ]);
    BackgroundOperation::create([
        'operation_key' => 'alert:reconcile-test',
        'type' => 'admin_alert',
        'payload' => [
            'type' => 'security_alert',
            'title' => 'Recovered',
            'body' => 'Recovered durable notification.',
        ],
        'available_at' => $now,
    ]);

    $this->artisan('background:reconcile')
        ->expectsOutput('Redispatched 2 pending background operation(s).')
        ->assertSuccessful();

    Queue::assertPushed(RecordUserActivities::class, fn (RecordUserActivities $job) => $job->operationKey === 'activity:reconcile-test'
        && $job->userId === 42
        && $job->productIds === [10, 11]);
    Queue::assertPushed(ProcessAdminAlert::class, fn (ProcessAdminAlert $job) => $job->operationKey === 'alert:reconcile-test');
});

it('leaves durable work pending when reconciliation cannot reach the queue', function () {
    BackgroundOperation::create([
        'operation_key' => 'alert:queue-outage',
        'type' => 'admin_alert',
        'payload' => [
            'type' => 'security_alert',
            'title' => 'Pending',
            'body' => 'Retry after the queue recovers.',
        ],
        'available_at' => now(),
    ]);
    $dispatcher = Mockery::mock(ReliableJobDispatcher::class);
    $dispatcher->shouldReceive('dispatch')->once()->andReturnFalse();
    app()->instance(ReliableJobDispatcher::class, $dispatcher);

    $this->artisan('background:reconcile')
        ->expectsOutput('Redispatched 0 pending background operation(s).')
        ->assertSuccessful();

    expect(BackgroundOperation::where('operation_key', 'alert:queue-outage')
        ->whereNull('processed_at')->exists())->toBeTrue();
});

it('records a queue heartbeat and detects a missing worker', function () {
    Cache::forget(QueueMonitor::HEARTBEAT_KEY);
    $this->artisan('queue:health', ['--max-age' => 120])->assertFailed();

    app()->call([new QueueHeartbeat, 'handle']);

    $this->artisan('queue:health', ['--max-age' => 120])->assertSuccessful();
});

it('moves recipient queries and notification writes out of the observer path', function () {
    Queue::fake();
    User::factory()->create(['role' => 'super_admin', 'is_active' => true]);
    $queries = [];
    DB::listen(function ($query) use (&$queries): void {
        $queries[] = strtolower($query->sql);
    });

    app(SuperAdminNotifier::class)->alert(
        'security_alert',
        'Deferred alert',
        'Materialize this outside the request.',
        '/tabs/logs',
        [],
        'test-alert:request-path',
    );

    expect(collect($queries)->filter(fn (string $sql) => str_contains($sql, 'background_operations')
        && preg_match('/^insert/', ltrim($sql)))->count())->toBe(1)
        ->and(collect($queries)->filter(fn (string $sql) => str_contains($sql, 'notifications')
            || str_contains($sql, 'mobile_push_devices')
            || str_contains($sql, 'mobile_push_deliveries'))->count())->toBe(0);
});

it('deduplicates repeated low inventory checks across job retries and events', function () {
    Queue::fake();
    config(['mobile_push.enabled' => false]);
    User::factory()->create(['role' => 'super_admin', 'is_active' => true]);
    $category = DB::table('categories')->insertGetId(['category_name' => 'Inventory'], 'category_id');
    $brand = Brand::create(['brand_name' => 'Inventory Brand']);
    $type = ShoeType::create(['shoe_type_name' => 'Inventory Type']);
    $product = Product::create([
        'category_id' => $category,
        'brand_id' => $brand->brand_id,
        'shoe_type_id' => $type->shoe_type_id,
        'product_name' => 'Inventory Product',
        'is_active' => true,
    ]);
    $variant = ProductVariant::create([
        'product_id' => $product->product_id,
        'size' => '9',
        'color' => 'Black',
    ]);
    Stock::create([
        'product_variant_id' => $variant->product_variant_id,
        'price' => 100,
        'received_quantity' => 5,
        'remaining_quantity' => 5,
        'deliver_date' => now()->toDateString(),
        'is_archived' => false,
    ]);

    app(SuperAdminNotifier::class)->queueInventoryCheck($variant->product_variant_id);
    app(SuperAdminNotifier::class)->queueInventoryCheck($variant->product_variant_id);

    foreach (BackgroundOperation::where('type', 'inventory_check')->get() as $operation) {
        $job = new ProcessAdminAlert($operation->operation_key);
        app()->call([$job, 'handle']);
        app()->call([$job, 'handle']);
    }

    $this->assertDatabaseCount('notifications', 1);
    $this->assertDatabaseCount('inventory_alert_states', 1);
    expect(BackgroundOperation::where('type', 'inventory_check')->whereNull('processed_at')->count())->toBe(0);
});

it('keeps a materialized alert recoverable when immediate push dispatch fails', function () {
    config([
        'mobile_push.enabled' => true,
        'mobile_push.auto_dispatch' => true,
    ]);
    User::factory()->create(['role' => 'super_admin', 'is_active' => true]);
    app(SuperAdminNotifier::class)->alert(
        'security_alert',
        'Queue outage',
        'The in-app notification must survive.',
        '/tabs/logs',
        [],
        'test-alert:push-dispatch-failure',
    );
    $operation = BackgroundOperation::where('operation_key', 'test-alert:push-dispatch-failure')->sole();
    $dispatcher = Mockery::mock(Dispatcher::class);
    $dispatcher->shouldReceive('dispatch')->with(Mockery::type(DeliverMobilePush::class))
        ->andThrow(new RuntimeException('queue unavailable'));
    app()->instance(Dispatcher::class, $dispatcher);

    app()->call([new ProcessAdminAlert($operation->operation_key), 'handle']);

    $this->assertDatabaseCount('notifications', 1);
    expect($operation->fresh()->processed_at)->not->toBeNull();
});
