<?php

use App\Models\User;
use App\Services\OrderService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

function checkoutAddressData(array $overrides = []): array
{
    return array_merge([
        'full_name' => 'Checkout Customer', 'phone_number' => '09171234567',
        'street' => '123 Example Street', 'barangay' => 'San Antonio',
        'city' => 'Quezon City', 'province' => 'Metro Manila',
        'region' => 'NCR', 'postal_code' => '1100',
    ], $overrides);
}

beforeEach(function () {
    Http::preventStrayRequests();
    $this->customer = User::factory()->create(['role' => 'user', 'email_verified_at' => now()]);
    $this->actingAs($this->customer);
    $category = DB::table('categories')->insertGetId(['category_name' => 'Checkout category'], 'category_id');
    $brand = DB::table('brands')->insertGetId(['brand_name' => 'Checkout brand'], 'brand_id');
    $type = DB::table('shoe_types')->insertGetId(['shoe_type_name' => 'Checkout type'], 'shoe_type_id');
    $product = DB::table('products')->insertGetId([
        'product_name' => 'Checkout Runner', 'category_id' => $category,
        'brand_id' => $brand, 'shoe_type_id' => $type,
    ], 'product_id');
    $variant = DB::table('product_variants')->insertGetId(['product_id' => $product, 'size' => '9', 'color' => 'Black'], 'product_variant_id');
    $this->variant = $variant;
    $cart = DB::table('carts')->insertGetId(['user_id' => $this->customer->id, 'status' => 0], 'cart_id');
    DB::table('cart_items')->insert(['cart_id' => $cart, 'product_variant_id' => $variant, 'quantity' => 2, 'price' => 2500]);
});

it('shows add address and a visible warning when arriving from cart without addresses', function () {
    $response = $this->get('/checkout')->assertOk()
        ->assertSee('id="addCheckoutAddress"', false)
        ->assertSee('id="checkoutAddressModal"', false)
        ->assertSee('Please add a delivery address before completing your order.')
        ->assertSee('₱5,000.00')
        ->assertSee('name="return_to" value="checkout"', false);
    $dom = new DOMDocument;
    @$dom->loadHTML($response->getContent());
    $xpath = new DOMXPath($dom);
    expect($xpath->query('//form[@id="checkoutForm"]//button[@type="submit"]')->length)->toBe(1)
        ->and($xpath->query('//form[@id="checkoutForm"]//form')->length)->toBe(0)
        ->and($xpath->query('//dialog//form[@id="checkoutAddressForm"]')->length)->toBe(1);
});

it('saves an address and returns to checkout with that address selected', function () {
    $this->from('/checkout')->post('/addresses', checkoutAddressData(['return_to' => 'checkout']))
        ->assertRedirect('/checkout')->assertSessionHas('success');
    $address = $this->customer->addresses()->firstOrFail();
    $this->assertSame('Checkout Customer', $address->full_name);
    $response = $this->get('/checkout')->assertOk()->assertSee('Address added and selected for this order.');
    $this->assertMatchesRegularExpression('/value="'.$address->address_id.'"\s+class="saved-address" checked/', $response->getContent());
    $this->assertDatabaseCount('cart_items', 1);
    $this->assertDatabaseCount('orders', 0);
});

it('can add another address while retaining the original and selecting the new one', function () {
    $original = $this->customer->addresses()->create(checkoutAddressData(['is_default' => true]));
    $this->get('/checkout')->assertOk()->assertSee('id="addCheckoutAddress"', false);
    $this->post('/addresses', checkoutAddressData(['return_to' => 'checkout', 'street' => '456 New Street']))
        ->assertRedirect('/checkout');
    $this->assertDatabaseCount('user_addresses', 2);
    $this->assertTrue($original->fresh()->is_default);
    $new = $this->customer->addresses()->where('address_id', '!=', $original->address_id)->firstOrFail();
    $response = $this->get('/checkout')->assertOk();
    $this->assertMatchesRegularExpression('/value="'.$new->address_id.'"\s+class="saved-address" checked/', $response->getContent());
});

it('reopens the modal with entered data and errors when the address is incomplete', function () {
    $this->from('/checkout')->post('/addresses', ['return_to' => 'checkout', 'full_name' => 'Keep this name'])
        ->assertRedirect('/checkout')->assertSessionHasErrors(['phone_number', 'street', 'city']);
    $this->get('/checkout')->assertOk()
        ->assertSee('data-reopen="true"', false)->assertSee('value="Keep this name"', false);
    $this->assertDatabaseCount('user_addresses', 0);
});

it('shows a useful warning and creates no order when an address is omitted', function () {
    $this->from('/checkout')->post('/checkout/place-order', [])
        ->assertRedirect('/checkout')
        ->assertSessionHasErrors(['address_id' => 'Please add or select a delivery address before completing your order.']);
    $this->get('/checkout')->assertOk()->assertSee('Please add or select a delivery address before completing your order.');
    $this->assertDatabaseCount('orders', 0);
});

it('rejects another customers address before starting payment', function () {
    $other = User::factory()->create();
    $address = $other->addresses()->create(checkoutAddressData());
    $this->from('/checkout')->post('/checkout/place-order', ['address_id' => $address->address_id])
        ->assertRedirect('/checkout')->assertSessionHasErrors('address_id');
    $this->assertDatabaseCount('orders', 0);
});

it('switches the default only when requested from the checkout modal', function () {
    $old = $this->customer->addresses()->create(checkoutAddressData(['is_default' => true]));
    $this->post('/addresses', checkoutAddressData(['return_to' => 'checkout', 'is_default' => '1']))
        ->assertRedirect('/checkout');
    $this->assertFalse($old->fresh()->is_default);
    $this->assertSame(1, $this->customer->addresses()->where('is_default', true)->count());
});
it('reprices cart items from trusted stock when creating an order', function () {
    DB::table('stocks')->insert([
        'product_variant_id' => $this->variant,
        'price' => 3000,
        'received_quantity' => 5,
        'remaining_quantity' => 5,
        'deliver_date' => '2026-09-01',
    ]);
    DB::table('cart_items')->update(['price' => 1]);
    $address = $this->customer->addresses()->create(checkoutAddressData());

    $order = app(OrderService::class)->createPendingOrderFromCart(
        $this->customer,
        $address->address_id
    );

    expect((float) $order->total_amount)->toBe(6000.0)
        ->and((float) $order->items->first()->price)->toBe(3000.0);
});
