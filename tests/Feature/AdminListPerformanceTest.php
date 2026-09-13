<?php

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

function seedAdminListCatalog(int $products = 1, int $variantsPerProduct = 1, int $stocksPerVariant = 1): array
{
    $now = now();
    $categoryId = DB::table('categories')->insertGetId([
        'category_name' => 'Admin List Category',
        'is_active' => true,
        'created_at' => $now,
        'updated_at' => $now,
    ], 'category_id');
    $brandId = DB::table('brands')->insertGetId([
        'brand_name' => 'Admin List Brand',
        'is_active' => true,
        'created_at' => $now,
        'updated_at' => $now,
    ], 'brand_id');
    $shoeTypeId = DB::table('shoe_types')->insertGetId([
        'shoe_type_name' => 'Admin List Type',
        'is_active' => true,
        'display_order' => 1,
        'created_at' => $now,
        'updated_at' => $now,
    ], 'shoe_type_id');
    $productIds = [];
    $variantIds = [];

    foreach (range(1, $products) as $productNumber) {
        $productId = DB::table('products')->insertGetId([
            'category_id' => $categoryId,
            'brand_id' => $brandId,
            'shoe_type_id' => $shoeTypeId,
            'product_name' => sprintf('Admin Shoe %03d', $productNumber),
            'product_description' => 'Performance fixture',
            'is_active' => true,
            'new_arrival_until' => $now->copy()->addDay(),
            'created_at' => $now,
            'updated_at' => $now,
        ], 'product_id');
        $productIds[] = $productId;

        foreach (range(1, $variantsPerProduct) as $variantNumber) {
            $variantId = DB::table('product_variants')->insertGetId([
                'product_id' => $productId,
                'size' => (string) $variantNumber,
                'color' => 'Color '.sprintf('%03d', $variantNumber),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ], 'product_variant_id');
            $variantIds[] = $variantId;

            foreach (range(1, $stocksPerVariant) as $stockNumber) {
                DB::table('stocks')->insert([
                    'product_variant_id' => $variantId,
                    'price' => 100 + $stockNumber,
                    'received_quantity' => 10,
                    'remaining_quantity' => $variantNumber % 7,
                    'deliver_date' => $now->copy()->subDays($stockNumber)->toDateString(),
                    'is_archived' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    return compact('categoryId', 'brandId', 'shoeTypeId', 'productIds', 'variantIds');
}

function seedAdminListOrders(User $customer, int $count, int $variantId): void
{
    foreach (range(1, $count) as $number) {
        $orderId = DB::table('orders')->insertGetId([
            'user_id' => $customer->id,
            'sale_type' => $number % 2 ? 'online' : 'pos',
            'total_amount' => 100 + $number,
            'status' => $number % 3 ? 'completed' : 'pending',
            'full_name' => $customer->full_name,
            'phone_number' => '09170000000',
            'street' => 'Admin Street',
            'barangay' => 'Admin',
            'city' => 'Manila',
            'province' => 'Metro Manila',
            'postal_code' => '1000',
            'created_at' => now()->subMinutes($number),
            'updated_at' => now()->subMinutes($number),
        ], 'order_id');
        DB::table('order_items')->insert([
            'order_id' => $orderId,
            'product_variant_id' => $variantId,
            'quantity' => 2,
            'price' => 100,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

function adminListQueryCount(Closure $callback): int
{
    DB::flushQueryLog();
    DB::enableQueryLog();
    $callback();
    $count = count(DB::getQueryLog());
    DB::disableQueryLog();

    return $count;
}

beforeEach(function () {
    $this->withoutVite();
    $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $this->superAdmin = User::factory()->create(['role' => 'super_admin', 'is_active' => true]);
});

it('paginates and filters the inventory while keeping global SQL totals', function () {
    seedAdminListCatalog(1, 61);
    $response = null;
    $queries = adminListQueryCount(function () use (&$response) {
        $response = $this->actingAs($this->admin)->get(route('admin.inventory.index'));
    });

    $response->assertOk()
        ->assertViewHas('inventory', fn ($rows) => $rows instanceof LengthAwarePaginator
            && $rows->perPage() === 25 && $rows->total() === 61 && $rows->count() === 25)
        ->assertViewHas('totalProducts', 61)
        ->assertViewHas('lowStock', 45)
        ->assertViewHas('outOfStock', 8);
    expect($queries)->toBe(3);

    $this->get(route('admin.inventory.index', ['page' => 3]))
        ->assertViewHas('inventory', fn ($rows) => $rows->currentPage() === 3 && $rows->count() === 11);

    $this->get(route('admin.inventory.index', ['search' => 'Color 030']))
        ->assertOk()
        ->assertViewHas('inventory', fn ($rows) => $rows->total() === 1 && $rows->first()->color === 'Color 030')
        ->assertViewHas('totalProducts', 61);
    $this->get(route('admin.inventory.index', ['status' => 'out-stock']))
        ->assertViewHas('inventory', fn ($rows) => $rows->total() === 8);
    $this->get(route('admin.inventory.index', ['search' => 'missing']))
        ->assertViewHas('inventory', fn ($rows) => $rows->isEmpty() && $rows->total() === 0);
    $this->get(route('admin.inventory.index', ['search' => '%']))
        ->assertViewHas('inventory', fn ($rows) => $rows->isEmpty() && $rows->total() === 0);
});

it('paginates active variants and keeps the color-size creation options', function () {
    $fixture = seedAdminListCatalog(1, 61);
    $product = Product::findOrFail($fixture['productIds'][0]);
    $response = null;
    $queries = adminListQueryCount(function () use ($product, &$response) {
        $response = $this->actingAs($this->admin)->get(route('admin.products.variants.index', [
            'product' => $product,
            'page' => 2,
        ]));
    });

    $response->assertOk()
        ->assertViewHas('variants', fn ($rows) => $rows instanceof LengthAwarePaginator
            && $rows->currentPage() === 2 && $rows->perPage() === 20 && $rows->total() === 61)
        ->assertViewHas('variantColors', fn ($rows) => $rows->count() === 61);
    expect($queries)->toBe(6);
});

it('paginates stock batches and uses aggregates for stock and variant navigation', function () {
    $fixture = seedAdminListCatalog(1, 30, 3);
    $variant = ProductVariant::findOrFail($fixture['variantIds'][0]);
    $response = null;
    $queries = adminListQueryCount(function () use ($variant, &$response) {
        $response = $this->actingAs($this->admin)->get(route('admin.stocks.index', [
            'variant' => $variant,
            'stock_page' => 1,
            'variant_page' => 2,
        ]));
    });

    $response->assertOk()
        ->assertViewHas('stocks', fn ($rows) => $rows instanceof LengthAwarePaginator
            && $rows->perPage() === 20 && $rows->total() === 3)
        ->assertViewHas('productVariants', fn ($rows) => $rows instanceof LengthAwarePaginator
            && $rows->perPage() === 24 && $rows->total() === 30
            && ! $rows->first()->relationLoaded('stocks')
            && array_key_exists('available_stock', $rows->first()->getAttributes()))
        ->assertViewHas('stockSummary', fn ($summary) => (int) $summary->entry_count === 3);
    expect($queries)->toBe(6);
});

it('keeps product pages bounded without hydrating stock histories', function () {
    seedAdminListCatalog(12, 3, 2);
    $response = null;
    $queries = adminListQueryCount(function () use (&$response) {
        $response = $this->actingAs($this->admin)->get(route('admin.products.index', ['page' => 2]));
    });

    $response->assertOk()->assertViewHas('products', function ($products) {
        $variant = $products->first()->variants->first();

        return $products->perPage() === 5
            && $products->total() === 12
            && ! $variant->relationLoaded('stocks')
            && array_key_exists('available_stock', $variant->getAttributes());
    });
    expect($queries)->toBe(7);

    $this->get(route('admin.products.index', [
        'search' => 'Admin Shoe 012',
    ]))->assertViewHas('products', fn ($products) => $products->total() === 1);
});

it('paginates orders with item counts instead of full item models', function () {
    $fixture = seedAdminListCatalog();
    $customer = User::factory()->create(['role' => 'user']);
    seedAdminListOrders($customer, 45, $fixture['variantIds'][0]);
    $response = null;
    $queries = adminListQueryCount(function () use (&$response) {
        $response = $this->actingAs($this->admin)->get(route('admin.orders.index', [
            'sale_type' => 'online',
            'page' => 2,
        ]));
    });

    $response->assertOk()->assertViewHas('orders', function ($orders) {
        $order = $orders->first();

        return $orders->perPage() === 20
            && $orders->total() === 23
            && ! $order->relationLoaded('items')
            && array_key_exists('items_count', $order->getAttributes());
    });
    expect($queries)->toBe(4);
});

it('keeps the live superadmin account list paginated and selects displayed fields only', function () {
    User::factory()->count(51)->create(['role' => 'user', 'is_active' => true]);
    $response = null;
    $queries = adminListQueryCount(function () use (&$response) {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.users.index', ['page' => 3]));
    });

    $response->assertOk()->assertViewHas('users', function ($users) {
        $attributes = array_keys($users->first()->getAttributes());

        return $users->currentPage() === 3
            && $users->perPage() === 25
            && $users->total() === 53
            && ! in_array('password', $attributes, true)
            && ! in_array('remember_token', $attributes, true);
    });
    expect($queries)->toBe(2);

    $this->get(route('admin.users.index', ['search' => $this->admin->email]))
        ->assertViewHas('users', fn ($users) => $users->total() === 1 && $users->first()->id === $this->admin->id);
    $this->actingAs($this->admin)->get(route('admin.users.index'))->assertForbidden();
});

it('adds only indexes used by the audited admin list filters and sorting', function () {
    expect(Schema::hasIndex('orders', ['created_at']))->toBeTrue()
        ->and(Schema::hasIndex('orders', ['sale_type', 'created_at']))->toBeTrue()
        ->and(Schema::hasIndex('products', ['is_active', 'product_name']))->toBeTrue()
        ->and(Schema::hasIndex('stocks', ['product_variant_id', 'is_archived', 'created_at']))->toBeTrue();
});
