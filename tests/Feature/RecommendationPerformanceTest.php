<?php

use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\ShoeType;
use App\Models\Stock;
use App\Models\User;
use App\Services\RecommendationClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->withoutVite();
    Cache::flush();
    config([
        'services.recommendation.url' => 'http://recommendation.internal:5000',
        'services.recommendation.key' => 'shared-test-key',
        'services.recommendation.cache_minutes' => 10,
    ]);
});

it('renders the main page without waiting for a recommendation cache miss', function () {
    $user = User::factory()->create(['role' => 'user', 'email_verified_at' => now()]);
    Http::fake();

    $this->actingAs($user)->get('/home')->assertOk()
        ->assertSee('data-recommendations-pending="1"', false);

    Http::assertNothingSent();
});

it('loads and caches recommendation cards through the deferred endpoint', function () {
    $user = User::factory()->create(['role' => 'user', 'email_verified_at' => now()]);
    DB::table('categories')->insert(['category_id' => 1, 'category_name' => 'Recommended']);
    $brand = Brand::create(['brand_name' => 'Lean Brand', 'is_active' => true]);
    $type = ShoeType::create(['shoe_type_name' => 'Lean Type', 'is_active' => true, 'display_order' => 0]);
    $product = Product::create([
        'category_id' => 1,
        'brand_id' => $brand->brand_id,
        'shoe_type_id' => $type->shoe_type_id,
        'product_name' => 'Lean Recommendation',
        'product_description' => 'This field is intentionally not selected.',
        'is_active' => true,
    ]);
    ProductImage::create([
        'product_id' => $product->product_id,
        'image_path' => 'primary.jpg',
        'is_primary' => true,
        'display_order' => 0,
    ]);
    ProductImage::create([
        'product_id' => $product->product_id,
        'image_path' => 'secondary.jpg',
        'is_primary' => false,
        'display_order' => 1,
    ]);
    $firstVariant = ProductVariant::create([
        'product_id' => $product->product_id,
        'size' => '9',
        'color' => 'Black',
        'is_active' => true,
    ]);
    $secondVariant = ProductVariant::create([
        'product_id' => $product->product_id,
        'size' => '10',
        'color' => 'White',
        'is_active' => true,
    ]);
    Stock::create([
        'product_variant_id' => $firstVariant->product_variant_id,
        'price' => 120,
        'received_quantity' => 1,
        'remaining_quantity' => 1,
        'deliver_date' => '2026-09-12',
        'is_archived' => false,
    ]);
    Stock::create([
        'product_variant_id' => $secondVariant->product_variant_id,
        'price' => 50,
        'received_quantity' => 1,
        'remaining_quantity' => 1,
        'deliver_date' => '2026-09-13',
        'is_archived' => false,
    ]);
    Http::fake(['*' => Http::response(['product_ids' => [$product->product_id]])]);
    $catalogReads = [];
    DB::listen(function ($query) use (&$catalogReads) {
        if (str_starts_with(strtolower(ltrim($query->sql)), 'select')
            && preg_match('/(?:products|brands|product_images)/i', $query->sql)) {
            $catalogReads[] = $query->sql;
        }
    });

    $this->actingAs($user)->get('/recommendations/cards')->assertOk()
        ->assertSee('Lean Recommendation')->assertSee('primary.jpg')->assertDontSee('secondary.jpg');
    expect($catalogReads)->toHaveCount(3);
    $this->get('/recommendations/cards')->assertOk()->assertSee('Lean Recommendation');

    Http::assertSentCount(1);
    $card = app(RecommendationClient::class)->cachedForUser($user->id)->sole();
    expect(array_keys($card->getAttributes()))->not->toContain('product_description')
        ->and($card->relationLoaded('variants'))->toBeFalse()
        ->and($card->relationLoaded('images'))->toBeFalse()
        ->and($card->primaryImage->image_path)->toBe('primary.jpg')
        ->and((float) $card->display_price)->toBe(120.0);
});

it('invalidates every recommendation limit with one version update', function () {
    $user = User::factory()->create();
    Http::fake(['recommendation.internal/*' => Http::response(['product_ids' => []])]);
    $client = app(RecommendationClient::class);
    $client->forUser($user->id, 8);
    $client->forUser($user->id, 12);
    Http::assertSentCount(2);

    $client->forgetForUser($user->id);
    $client->forUser($user->id, 8);
    $client->forUser($user->id, 12);
    Http::assertSentCount(4);
});

it('keeps the current product out of deferred product-page recommendations', function () {
    $user = User::factory()->create(['role' => 'user', 'email_verified_at' => now()]);
    DB::table('categories')->insert(['category_id' => 1, 'category_name' => 'Recommended']);
    $brand = Brand::create(['brand_name' => 'Exclude Brand', 'is_active' => true]);
    $type = ShoeType::create(['shoe_type_name' => 'Exclude Type', 'is_active' => true, 'display_order' => 0]);
    $current = Product::create([
        'category_id' => 1,
        'brand_id' => $brand->brand_id,
        'shoe_type_id' => $type->shoe_type_id,
        'product_name' => 'Current Product',
        'is_active' => true,
    ]);
    $other = Product::create([
        'category_id' => 1,
        'brand_id' => $brand->brand_id,
        'shoe_type_id' => $type->shoe_type_id,
        'product_name' => 'Other Product',
        'is_active' => true,
    ]);
    Http::fake(['*' => Http::response(['product_ids' => [$current->product_id, $other->product_id]])]);

    $this->actingAs($user)
        ->get(route('recommendations.cards', ['exclude_product_id' => $current->product_id]))
        ->assertOk()
        ->assertDontSee('Current Product')
        ->assertSee('Other Product');
});

it('returns an empty optional section when the recommendation service is unavailable', function () {
    $user = User::factory()->create(['role' => 'user', 'email_verified_at' => now()]);
    Http::fake(fn () => throw new ConnectionException('timed out'));

    $this->actingAs($user)->get('/recommendations/cards')
        ->assertOk()
        ->assertSee('No recommendations yet');
});
