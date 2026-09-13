<?php

use App\Http\Controllers\Api\AnalyticsController;
use App\Models\Brand;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShoeType;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\AnalyticsService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

function seedDashboardReportData(): array
{
    $categoryId = DB::table('categories')->insertGetId([
        'category_name' => 'Analytics',
        'created_at' => now(),
        'updated_at' => now(),
    ], 'category_id');
    $brand = Brand::create(['brand_name' => 'Analytics Brand']);
    $type = ShoeType::create(['shoe_type_name' => 'Analytics Type']);
    $customers = User::factory()->count(4)->create(['role' => 'user', 'is_active' => true]);
    DB::table('users')->where('id', $customers->first()->id)->update([
        'created_at' => '2025-09-13 08:00:00',
        'updated_at' => '2025-09-13 08:00:00',
    ]);
    $products = collect();
    $variants = collect();

    foreach (range(1, 12) as $number) {
        $product = Product::create([
            'category_id' => $categoryId,
            'brand_id' => $brand->brand_id,
            'shoe_type_id' => $type->shoe_type_id,
            'product_name' => 'Analytics Shoe '.$number,
            'is_active' => true,
        ]);
        if ($number === 1) {
            DB::table('products')->where('product_id', $product->product_id)->update([
                'created_at' => '2025-09-13 08:00:00',
                'updated_at' => '2025-09-13 08:00:00',
            ]);
        }
        $variant = ProductVariant::create([
            'product_id' => $product->product_id,
            'size' => (string) (6 + $number),
            'color' => 'Color '.$number,
        ]);
        $stock = Stock::create([
            'product_variant_id' => $variant->product_variant_id,
            'price' => 100 + $number,
            'received_quantity' => 20,
            'remaining_quantity' => $number % 4,
            'deliver_date' => now()->subDays($number)->toDateString(),
            'is_archived' => false,
        ]);
        StockMovement::create([
            'stock_id' => $stock->stock_id,
            'quantity' => $number,
            'type' => 'in',
            'created_at' => now()->subMinutes($number),
            'updated_at' => now()->subMinutes($number),
        ]);
        $products->push($product);
        $variants->push($variant);
    }

    $dates = collect([
        '2025-01-15 10:00:00',
        '2025-09-05 10:00:00',
        '2025-09-20 10:00:00',
        '2026-08-15 10:00:00',
        '2026-09-07 10:00:00',
        '2026-09-08 10:00:00',
        '2026-09-09 10:00:00',
        '2026-09-10 10:00:00',
        '2026-09-11 10:00:00',
        '2026-09-12 10:00:00',
        '2026-09-13 10:00:00',
        '2025-08-15 10:00:00',
    ]);
    $orderIds = collect();

    foreach ($dates as $index => $date) {
        $orderId = DB::table('orders')->insertGetId([
            'user_id' => $customers[$index % $customers->count()]->id,
            'sale_type' => 'pos',
            'total_amount' => 1000 + ($index * 100),
            'status' => $index === 3 ? 'paid' : 'completed',
            'full_name' => 'Analytics Customer',
            'phone_number' => '09170000000',
            'street' => 'Analytics Street',
            'barangay' => 'Analytics',
            'city' => $index % 2 ? 'Quezon City' : 'Manila',
            'province' => 'Metro Manila',
            'postal_code' => '1000',
            'created_at' => $date,
            'updated_at' => $date,
        ], 'order_id');
        DB::table('order_items')->insert([
            'order_id' => $orderId,
            'product_variant_id' => $variants[$index % $variants->count()]->product_variant_id,
            'quantity' => $index + 1,
            'price' => 100 + $index,
            'created_at' => $date,
            'updated_at' => $date,
        ]);
        $orderIds->push($orderId);
    }

    return compact('customers', 'products', 'variants', 'orderIds');
}

function requestQueryCount(Closure $request): int
{
    DB::flushQueryLog();
    DB::enableQueryLog();
    $request();
    $count = count(DB::getQueryLog());
    DB::disableQueryLog();

    return $count;
}

beforeEach(function () {
    CarbonImmutable::setTestNow('2026-09-13 12:00:00');
    Cache::flush();
    $this->withoutVite();
    $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $this->fixture = seedDashboardReportData();
});

afterEach(function () {
    CarbonImmutable::setTestNow();
});

it('loads the dashboard with bounded queries and bounded collections', function () {
    $response = null;
    $queries = requestQueryCount(function () use (&$response) {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));
    });

    $response->assertOk()
        ->assertViewHas('newProducts', 11)
        ->assertViewHas('newUsers', 4)
        ->assertViewHas('ordersGrowth', 600.0)
        ->assertViewHas('topProducts', fn ($rows) => $rows->count() <= 5)
        ->assertViewHas('topShoeTypes', fn ($rows) => $rows->count() <= 5)
        ->assertViewHas('lowStockProducts', fn ($rows) => $rows->count() <= 5)
        ->assertViewHas('recentOrders', fn ($rows) => $rows->count() <= 5);

    expect($queries)->toBe(13)
        ->and(array_key_exists('recentStockMovements', $response->viewData()))->toBeFalse();
});

it('uses the selected report year and limits ranked data', function () {
    $response = null;
    $queries = requestQueryCount(function () use (&$response) {
        $response = $this->actingAs($this->admin)->get(route('admin.reports.index', ['year' => 2025]));
    });

    $response->assertOk()
        ->assertViewHas('selectedYear', 2025)
        ->assertViewHas('monthlySalesData', fn (array $values) => (float) $values[8] === 2300.0)
        ->assertViewHas('bestSellingProducts', fn ($rows) => $rows->count() <= 10)
        ->assertViewHas('topCustomers', fn ($rows) => $rows->count() <= 5);

    expect($queries)->toBe(10)
        ->and($response->viewData('bestSellingProducts'))->toHaveCount(10);
});

it('serves dashboard and report cache hits without analytics queries', function () {
    $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertOk();
    $dashboardHit = requestQueryCount(fn () => $this->get(route('admin.dashboard'))->assertOk());

    $this->get(route('admin.reports.index', ['year' => 2025]))->assertOk();
    $reportHit = requestQueryCount(
        fn () => $this->get(route('admin.reports.index', ['year' => 2025]))->assertOk()
    );

    expect($dashboardHit)->toBe(0)
        ->and($reportHit)->toBe(0)
        ->and(Cache::has(AnalyticsService::VERSION_KEY))->toBeTrue();
});

it('invalidates cached dashboard and report totals after an order commit', function () {
    $dashboard = $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertOk();
    $report = $this->get(route('admin.reports.index', ['year' => 2026]))->assertOk();
    $beforeDashboard = $dashboard->viewData('totalOrders');
    $beforeReport = $report->viewData('totalOrders');
    $customer = $this->fixture['customers']->first();

    Order::create([
        'user_id' => $customer->id,
        'sale_type' => 'pos',
        'status' => 'completed',
        'total_amount' => 500,
        'full_name' => 'Cache Customer',
        'phone_number' => '09170000001',
        'street' => 'Cache Street',
        'barangay' => 'Cache',
        'city' => 'Manila',
        'province' => 'Metro Manila',
        'postal_code' => '1000',
    ]);

    $this->get(route('admin.dashboard'))->assertViewHas('totalOrders', $beforeDashboard + 1);
    $this->get(route('admin.reports.index', ['year' => 2026]))
        ->assertViewHas('totalOrders', $beforeReport + 1);
});

it('does not invalidate analytics caches when a transaction rolls back', function () {
    app(AnalyticsService::class)->dashboard();
    $version = Cache::get(AnalyticsService::VERSION_KEY);
    $customer = $this->fixture['customers']->first();

    DB::beginTransaction();
    Order::create([
        'user_id' => $customer->id,
        'sale_type' => 'pos',
        'status' => 'completed',
        'total_amount' => 500,
        'full_name' => 'Rolled Back',
        'phone_number' => '09170000002',
        'street' => 'Rollback Street',
        'barangay' => 'Rollback',
        'city' => 'Manila',
        'province' => 'Metro Manila',
        'postal_code' => '1000',
    ]);
    DB::rollBack();

    expect(Cache::get(AnalyticsService::VERSION_KEY))->toBe($version);
});

it('shares the cached overview with the confirmed unrouted analytics controller', function () {
    $registered = collect(Route::getRoutes())->contains(
        fn ($route) => $route->getActionName() === AnalyticsController::class.'@index'
    );
    expect($registered)->toBeFalse();

    $analytics = app(AnalyticsService::class);
    $analytics->dashboard();
    $response = null;
    $queries = requestQueryCount(function () use ($analytics, &$response): void {
        $response = app(AnalyticsController::class)->index($analytics);
    });

    expect($queries)->toBe(1)
        ->and($response->getData(true))->toHaveKeys([
            'summary',
            'status_counts',
            'best_sellers',
            'monthly_revenue',
            'last_7_days',
        ])
        ->and($response->getData(true)['last_7_days'])->toHaveCount(7)
        ->and($response->getData(true)['best_sellers'])->toHaveCount(5);
});

it('keeps report access protected and PDF downloads synchronous', function () {
    auth()->logout();
    $this->get(route('admin.reports.index'))->assertRedirect(route('login'));
    $this->actingAs(User::factory()->create(['role' => 'user', 'is_active' => true]))
        ->get(route('admin.reports.index'))
        ->assertForbidden();

    $response = $this->actingAs($this->admin)
        ->get(route('admin.reports.export-pdf', ['year' => 2025]))
        ->assertOk();

    expect($response->headers->get('content-type'))->toBe('application/pdf')
        ->and($response->headers->get('content-disposition'))->toContain('sales-report-2025.pdf');
});

it('adds only the indexes consumed by analytics joins and date ranges', function () {
    expect(Schema::hasIndex('orders', ['status', 'created_at']))->toBeTrue()
        ->and(Schema::hasIndex('order_items', ['product_variant_id']))->toBeTrue();
});
