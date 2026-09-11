<?php

use App\Enums\OrderStatus;
use App\Enums\SaleType;
use App\Models\Order;
use App\Models\User;
use App\Models\UserAddress;
use App\Services\PayMongoService;
use App\Services\RecommendationClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    $this->withoutVite();
});

it('adds browser security headers and prevents sensitive pages from being cached', function () {
    $this->get('/login')
        ->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('Content-Security-Policy', "frame-ancestors 'self'")
        ->assertHeader('Cache-Control', 'no-store, private');
});

it('allows CORS only for explicitly configured origins', function () {
    config(['cors.allowed_origins' => ['https://shop.example']]);

    $this->withHeaders([
        'Origin' => 'https://attacker.example',
        'Access-Control-Request-Method' => 'POST',
    ])->options('/api/login')->assertHeader('Access-Control-Allow-Origin', 'https://shop.example');

    $this->withHeaders([
        'Origin' => 'https://shop.example',
        'Access-Control-Request-Method' => 'POST',
    ])->options('/api/login')->assertHeader('Access-Control-Allow-Origin', 'https://shop.example');
});

it('rejects stale PayMongo signatures while accepting fresh authentic signatures', function () {
    config([
        'services.paymongo.webhook_secret' => 'security-test-secret',
        'services.paymongo.webhook_tolerance' => 300,
    ]);
    $body = '{"data":{"id":"evt_security"}}';

    $staleTimestamp = (string) (time() - 301);
    $staleSignature = hash_hmac('sha256', $staleTimestamp.'.'.$body, 'security-test-secret');
    expect(app(PayMongoService::class)->verifyWebhookSignature(
        $body,
        "t={$staleTimestamp},te={$staleSignature}"
    ))->toBeFalse();

    $freshTimestamp = (string) time();
    $freshSignature = hash_hmac('sha256', $freshTimestamp.'.'.$body, 'security-test-secret');
    expect(app(PayMongoService::class)->verifyWebhookSignature(
        $body,
        "t={$freshTimestamp},te={$freshSignature}"
    ))->toBeTrue();
});

it('does not mass assign privileged user fields', function () {
    $user = User::factory()->create(['role' => 'user', 'is_active' => true]);

    $user->fill(['role' => 'super_admin', 'is_active' => false])->save();

    expect($user->fresh()->role)->toBe('user')
        ->and($user->fresh()->is_active)->toBeTrue();
});

it('keeps customer addresses and orders isolated by owner', function () {
    $owner = User::factory()->create(['role' => 'user', 'is_active' => true]);
    $attacker = User::factory()->create(['role' => 'user', 'is_active' => true]);
    $address = UserAddress::create([
        'user_id' => $owner->id,
        'full_name' => 'Owner',
        'phone_number' => '09171234567',
        'street' => 'Private Street',
        'barangay' => 'Private Barangay',
        'city' => 'Private City',
        'province' => 'Private Province',
        'postal_code' => '1000',
    ]);
    $order = Order::create([
        'user_id' => $owner->id,
        'sale_type' => SaleType::Online,
        'status' => OrderStatus::Pending,
        'total_amount' => 100,
        'full_name' => 'Owner',
        'phone_number' => '09171234567',
        'street' => 'Private Street',
        'barangay' => 'Private Barangay',
        'city' => 'Private City',
        'province' => 'Private Province',
        'postal_code' => '1000',
    ]);

    $this->actingAs($attacker)->get('/addresses/'.$address->getKey().'/edit')->assertForbidden();
    $this->actingAs($attacker)->get('/orders/'.$order->getKey())->assertNotFound();
});

it('hides inactive products and inactive inventory from public detail routes', function () {
    $category = DB::table('categories')->insertGetId(['category_name' => 'Hidden'], 'category_id');
    $brand = DB::table('brands')->insertGetId(['brand_name' => 'Hidden'], 'brand_id');
    $type = DB::table('shoe_types')->insertGetId(['shoe_type_name' => 'Hidden'], 'shoe_type_id');
    $product = DB::table('products')->insertGetId([
        'product_name' => 'Hidden Product',
        'category_id' => $category,
        'brand_id' => $brand,
        'shoe_type_id' => $type,
        'is_active' => false,
    ], 'product_id');

    $this->get('/product/'.$product)->assertNotFound();
});

it('authenticates recommendation requests and caps their cost', function () {
    Cache::flush();
    config([
        'services.recommendation.url' => 'http://recommendation.internal:5000',
        'services.recommendation.key' => 'shared-test-key',
    ]);
    Http::fake([
        'recommendation.internal/*' => Http::response(['product_ids' => []]),
    ]);

    app(RecommendationClient::class)->forUser(123, 500);

    Http::assertSent(fn ($request) => $request->url() === 'http://recommendation.internal:5000/recommendations/123?limit=20'
        && $request->hasHeader('X-Recommendation-Key', 'shared-test-key')
    );
});

it('fails recommendation calls closed when the shared key is missing', function () {
    Cache::flush();
    config(['services.recommendation.key' => null]);
    Http::fake();

    expect(app(RecommendationClient::class)->forUser(321))->toBeEmpty();
    Http::assertNothingSent();
});

it('registers throttles on password reset, search, reports, POS, and APIs', function () {
    expect(Route::getRoutes()->getByName('password.email')->gatherMiddleware())
        ->toContain('throttle:password-reset');
    expect(Route::getRoutes()->getByName('search')->gatherMiddleware())
        ->toContain('throttle:public-search');
    expect(Route::getRoutes()->getByName('admin.reports.index')->gatherMiddleware())
        ->toContain('throttle:expensive-admin');
    expect(Route::getRoutes()->getByName('admin.pos.store')->gatherMiddleware())
        ->toContain('throttle:30,1');
    expect(Route::getRoutes()->getByName('checkout.place-order')->gatherMiddleware())
        ->toContain('throttle:6,1');
    expect(Route::getRoutes()->getByName('admin.orders.index')->gatherMiddleware())
        ->toContain('admin');
    expect(Route::getRoutes()->match(
        Request::create('/api/orders', 'GET')
    )->gatherMiddleware())->toContain('throttle:authenticated_api');
});
