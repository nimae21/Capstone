<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\Guest\GuestController;
use App\Http\Controllers\PageController;
use App\Models\Brand;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\ShoeType;
use App\Models\Stock;
use App\Models\User;
use App\Services\CatalogCache;
use App\View\Components\HeroCarousel;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

function storefrontFixture(int $stockBatches = 3): array
{
    $category = Category::create(['category_name' => 'Performance']);
    $brand = Brand::create(['brand_name' => 'Achilles', 'is_active' => true]);
    $type = ShoeType::create(['shoe_type_name' => 'Runner', 'is_active' => true, 'display_order' => 1]);
    $product = Product::create([
        'category_id' => $category->category_id,
        'brand_id' => $brand->brand_id,
        'shoe_type_id' => $type->shoe_type_id,
        'product_name' => 'Velocity Runner',
        'product_description' => 'Fast shoe',
        'is_active' => true,
    ]);
    ProductImage::create([
        'product_id' => $product->product_id,
        'image_path' => 'runner.jpg',
        'is_primary' => true,
        'display_order' => 1,
    ]);
    $variant = ProductVariant::create([
        'product_id' => $product->product_id,
        'size' => '9',
        'color' => 'Black',
        'is_active' => true,
    ]);
    foreach (range(1, $stockBatches) as $batch) {
        Stock::create([
            'product_variant_id' => $variant->product_variant_id,
            'price' => 1000 + $batch,
            'received_quantity' => 5,
            'remaining_quantity' => 5,
            'deliver_date' => now()->subDays($stockBatches - $batch)->toDateString(),
            'is_archived' => false,
        ]);
    }

    return [$product, $variant];
}

it('loads product detail aggregates without hydrating stock histories', function () {
    [$product] = storefrontFixture(12);
    foreach (range(2, 10) as $number) {
        $variant = ProductVariant::create([
            'product_id' => $product->product_id,
            'size' => (string) $number,
            'color' => 'Color '.$number,
            'is_active' => true,
        ]);
        foreach (range(1, 12) as $batch) {
            Stock::create([
                'product_variant_id' => $variant->product_variant_id,
                'price' => 900 + $batch,
                'received_quantity' => 2,
                'remaining_quantity' => 2,
                'deliver_date' => now()->subDays(12 - $batch)->toDateString(),
                'is_archived' => false,
            ]);
        }
    }

    DB::enableQueryLog();
    DB::flushQueryLog();
    $view = app(PageController::class)->showProduct($product->product_id);
    $loaded = $view->getData()['product'];

    expect(DB::getQueryLog())->toHaveCount(5);
    DB::disableQueryLog();
    expect($loaded->variants)->toHaveCount(10)
        ->and($loaded->variants->first()->relationLoaded('stocks'))->toBeFalse()
        ->and((int) $loaded->variants->first()->available_stock)->toBe(60)
        ->and((float) $loaded->variants->first()->current_price)->toBe(1012.0);
});

it('uses card fields and omits variants and stock histories from search results', function () {
    storefrontFixture(10);

    DB::enableQueryLog();
    DB::flushQueryLog();
    $view = app(PageController::class)->search(Request::create('/search', 'GET', ['q' => 'velocity']));
    $products = $view->getData()['products'];

    expect(DB::getQueryLog())->toHaveCount(6);
    DB::disableQueryLog();
    expect($products)->toHaveCount(1)
        ->and($products->first()->images)->toHaveCount(1)
        ->and($products->first()->relationLoaded('variants'))->toBeFalse();
});

it('shares versioned guest and hero cache entries and invalidates by version', function () {
    [$product, $variant] = storefrontFixture();
    $user = User::factory()->create();
    $order = Order::create([
        'user_id' => $user->id,
        'sale_type' => 'online',
        'total_amount' => 1000,
        'status' => 'pending',
        'full_name' => 'Test User',
        'phone_number' => '09000000000',
        'street' => 'Street',
        'barangay' => 'Barangay',
        'city' => 'City',
        'province' => 'Province',
        'postal_code' => '1000',
    ]);
    OrderItem::create([
        'order_id' => $order->order_id,
        'product_variant_id' => $variant->product_variant_id,
        'quantity' => 2,
        'price' => 1000,
    ]);

    Cache::flush();
    app(GuestController::class)->index();
    DB::enableQueryLog();
    DB::flushQueryLog();
    $cached = app(GuestController::class)->index()->getData()['products'];
    expect(DB::getQueryLog())->toHaveCount(0)
        ->and($cached->first()->product_id)->toBe($product->product_id);
    DB::disableQueryLog();

    (new HeroCarousel)->render();
    DB::enableQueryLog();
    DB::flushQueryLog();
    (new HeroCarousel)->render();
    expect(DB::getQueryLog())->toHaveCount(0);
    DB::disableQueryLog();

    $before = app(CatalogCache::class)->version();
    app(CatalogCache::class)->invalidate();
    expect(app(CatalogCache::class)->version())->toBe($before + 1);
});

it('loads cart rows with aggregate stock and one image using a fixed query budget', function () {
    [$product, $variant] = storefrontFixture(20);
    $user = User::factory()->create();
    $cart = Cart::create(['user_id' => $user->id, 'status' => 0]);
    CartItem::create([
        'cart_id' => $cart->cart_id,
        'product_variant_id' => $variant->product_variant_id,
        'quantity' => 2,
        'price' => 1001,
    ]);
    $this->actingAs($user);

    DB::enableQueryLog();
    DB::flushQueryLog();
    $loaded = app(CartController::class)->index()->getData()['cart'];

    expect(DB::getQueryLog())->toHaveCount(5);
    DB::disableQueryLog();
    $item = $loaded->items->first();
    expect($item->variant->relationLoaded('stocks'))->toBeFalse()
        ->and((int) $item->variant->available_stock)->toBe(100)
        ->and($item->variant->product->images)->toHaveCount(1);
});

it('uses one header aggregate for customers and no cart query for guests or admins', function () {
    [$product, $variant] = storefrontFixture();
    $customer = User::factory()->create();
    $cart = Cart::create(['user_id' => $customer->id, 'status' => 0]);
    CartItem::create([
        'cart_id' => $cart->cart_id,
        'product_variant_id' => $variant->product_variant_id,
        'quantity' => 3,
        'price' => 1001,
    ]);

    $this->actingAs($customer);
    DB::enableQueryLog();
    DB::flushQueryLog();
    $html = view('partials.customer-header')->render();
    expect(DB::getQueryLog())->toHaveCount(1)
        ->and($html)->toContain('>3</span>');
    DB::disableQueryLog();

    auth()->logout();
    DB::enableQueryLog();
    DB::flushQueryLog();
    view('partials.customer-header')->render();
    expect(DB::getQueryLog())->toHaveCount(0);
    DB::disableQueryLog();

    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin);
    DB::enableQueryLog();
    DB::flushQueryLog();
    view('partials.customer-header')->render();
    expect(DB::getQueryLog())->toHaveCount(0);
    DB::disableQueryLog();
});

it('coalesces duplicate add submissions into one constrained cart item', function () {
    [$product, $variant] = storefrontFixture();
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->post(route('cart.add'), [
        'product_variant_id' => $variant->product_variant_id,
        'quantity' => 1,
    ])->assertRedirect(route('cart.index'));
    $this->post(route('cart.add'), [
        'product_variant_id' => $variant->product_variant_id,
        'quantity' => 2,
    ])->assertRedirect(route('cart.index'));

    expect(Cart::where('user_id', $user->id)->where('status', 0)->count())->toBe(1)
        ->and(CartItem::count())->toBe(1)
        ->and(CartItem::first()->quantity)->toBe(3);
});

it('enforces one active cart and one variant row per cart while allowing history', function () {
    [$product, $variant] = storefrontFixture();
    $user = User::factory()->create();
    $active = Cart::create(['user_id' => $user->id, 'status' => 0]);
    Cart::create(['user_id' => $user->id, 'status' => 1]);
    Cart::create(['user_id' => $user->id, 'status' => 1]);

    expect(fn () => Cart::create(['user_id' => $user->id, 'status' => 0]))
        ->toThrow(QueryException::class);

    CartItem::create([
        'cart_id' => $active->cart_id,
        'product_variant_id' => $variant->product_variant_id,
        'quantity' => 1,
        'price' => 1001,
    ]);
    expect(fn () => CartItem::create([
        'cart_id' => $active->cart_id,
        'product_variant_id' => $variant->product_variant_id,
        'quantity' => 1,
        'price' => 1001,
    ]))->toThrow(QueryException::class);
});

it('audits then merges bounded duplicates without losing quantity and supports rollback', function () {
    $migration = require database_path('migrations/2026_09_13_000007_enforce_cart_integrity.php');
    $migration->down();

    [$product, $variant] = storefrontFixture();
    $user = User::factory()->create();
    $first = Cart::create(['user_id' => $user->id, 'status' => 0]);
    $second = Cart::create(['user_id' => $user->id, 'status' => 0]);
    CartItem::create([
        'cart_id' => $first->cart_id,
        'product_variant_id' => $variant->product_variant_id,
        'quantity' => 1,
        'price' => 1001,
    ]);
    CartItem::create([
        'cart_id' => $second->cart_id,
        'product_variant_id' => $variant->product_variant_id,
        'quantity' => 2,
        'price' => 1001,
    ]);

    $this->artisan('carts:prepare-integrity')->assertFailed();
    expect(Cart::where('status', 0)->count())->toBe(2)
        ->and(CartItem::sum('quantity'))->toBe(3);

    $this->artisan('carts:prepare-integrity --apply --max-groups=10')->assertSuccessful();
    expect(Cart::where('status', 0)->count())->toBe(1)
        ->and(CartItem::count())->toBe(1)
        ->and(CartItem::first()->quantity)->toBe(3);

    $migration->up();
    expect(fn () => Cart::create(['user_id' => $user->id, 'status' => 0]))
        ->toThrow(QueryException::class);
});

it('refuses cleanup when merging an active cart would exceed current stock', function () {
    $migration = require database_path('migrations/2026_09_13_000007_enforce_cart_integrity.php');
    $migration->down();

    [$product, $variant] = storefrontFixture(1);
    $user = User::factory()->create();
    $first = Cart::create(['user_id' => $user->id, 'status' => 0]);
    $second = Cart::create(['user_id' => $user->id, 'status' => 0]);
    foreach ([[$first, 3], [$second, 3]] as [$cart, $quantity]) {
        CartItem::create([
            'cart_id' => $cart->cart_id,
            'product_variant_id' => $variant->product_variant_id,
            'quantity' => $quantity,
            'price' => 1001,
        ]);
    }

    $this->artisan('carts:prepare-integrity --apply --max-groups=10')->assertFailed();
    expect(Cart::where('status', 0)->count())->toBe(2)
        ->and(CartItem::sum('quantity'))->toBe(6);

    CartItem::where('cart_id', $second->cart_id)->delete();
    $second->delete();
    $migration->up();
});

it('keeps cart count quantity updates removal and checkout compatible', function () {
    [$product, $variant] = storefrontFixture();
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->post(route('cart.add'), [
        'product_variant_id' => $variant->product_variant_id,
        'quantity' => 1,
    ])->assertRedirect(route('cart.index'));

    $item = CartItem::firstOrFail();
    $this->patchJson(route('cart.increase', $item->cart_item_id))
        ->assertOk()
        ->assertJsonPath('quantity', 2)
        ->assertJsonPath('cart_count', 2);
    $this->patchJson(route('cart.decrease', $item->cart_item_id))
        ->assertOk()
        ->assertJsonPath('quantity', 1)
        ->assertJsonPath('cart_count', 1);
    $this->getJson(route('cart.count'))->assertOk()->assertJson(['count' => 1]);
    $this->get(route('checkout.index'))->assertOk();

    $this->delete(route('cart.remove', $item->cart_item_id))
        ->assertRedirect();
    expect(CartItem::count())->toBe(0);
});
