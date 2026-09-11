<?php

use App\Enums\OrderStatus;
use App\Exceptions\InvalidOrderTransitionException;
use App\Exceptions\OrderNotCancellableException;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\OrderService;
use App\Services\PayMongoService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

beforeEach(function () {
    Http::preventStrayRequests();
    config(['mobile_push.enabled' => false, 'services.paymongo.secret_key' => 'sk_test_fixture',
        'services.paymongo.webhook_secret' => 'whsec_fixture']);
});

function lifecycleOrder(string $saleType = 'online'): array
{
    $user = User::factory()->create(['role' => 'user', 'email_verified_at' => now()]);
    $category = DB::table('categories')->insertGetId(['category_name' => 'Lifecycle'], 'category_id');
    $brand = DB::table('brands')->insertGetId(['brand_name' => 'Lifecycle'], 'brand_id');
    $type = DB::table('shoe_types')->insertGetId(['shoe_type_name' => 'Lifecycle'], 'shoe_type_id');
    $product = DB::table('products')->insertGetId([
        'product_name' => 'Lifecycle Shoe', 'category_id' => $category, 'brand_id' => $brand, 'shoe_type_id' => $type,
    ], 'product_id');
    $variant = DB::table('product_variants')->insertGetId([
        'product_id' => $product, 'size' => '9', 'color' => 'Black',
    ], 'product_variant_id');
    $stock = DB::table('stocks')->insertGetId([
        'product_variant_id' => $variant, 'price' => 500, 'received_quantity' => 10,
        'remaining_quantity' => 10, 'deliver_date' => '2026-01-01',
    ], 'stock_id');
    $order = Order::create([
        'user_id' => $user->id, 'sale_type' => $saleType, 'status' => 'pending', 'total_amount' => 1000,
        'full_name' => 'Snapshot Recipient', 'phone_number' => '09171234567', 'street' => '123 Snapshot Street',
        'barangay' => 'Snapshot Barangay', 'city' => 'Quezon City', 'province' => 'Metro Manila', 'postal_code' => '1100',
    ]);
    $order->items()->create(['product_variant_id' => $variant, 'quantity' => 2, 'price' => 500]);
    $payment = Payment::create([
        'order_id' => $order->order_id, 'checkout_session_id' => 'cs_fixture',
        'method' => 'pending', 'status' => 'pending',
    ]);

    return compact('order', 'payment', 'stock', 'variant', 'user');
}

function lifecyclePaidResource(): array
{
    return ['id' => 'pay_fixture', 'type' => 'payment', 'attributes' => [
        'status' => 'paid', 'amount' => 100000, 'currency' => 'PHP', 'source' => ['type' => 'gcash'],
    ]];
}

function lifecycleSession(bool $paid = false, string $status = 'active'): array
{
    return ['id' => 'cs_fixture', 'type' => 'checkout_session',
        'attributes' => ['status' => $status, 'payments' => $paid ? [lifecyclePaidResource()] : []]];
}

function lifecycleRefund(string $status = 'pending', int $timestamp = 100): array
{
    return ['id' => 'ref_fixture', 'type' => 'refund', 'attributes' => [
        'payment_id' => 'pay_fixture', 'amount' => 100000, 'currency' => 'PHP',
        'status' => $status, 'created_at' => 100, 'updated_at' => $timestamp,
    ]];
}

function lifecycleWebhook($test, string $type, array $resource)
{
    $body = json_encode(['data' => ['id' => 'evt_fixture', 'attributes' => ['type' => $type, 'data' => $resource]]]);
    $timestamp = (string) time();
    $signature = hash_hmac('sha256', $timestamp.'.'.$body, 'whsec_fixture');

    return $test->call('POST', '/webhooks/paymongo', [], [], [], [
        'CONTENT_TYPE' => 'application/json', 'HTTP_PAYMONGO_SIGNATURE' => "t={$timestamp},te={$signature}",
    ], $body);
}

function lifecycleConfirm(): void
{
    app(OrderService::class)->confirmPayment('cs_fixture', 'gcash', 'pay_fixture', 100000);
}

it('sends the online order snapshot as billing without changing it', function () {
    $f = lifecycleOrder();
    Http::fake(['api.paymongo.com/v1/checkout_sessions' => Http::response([
        'data' => ['id' => 'cs_created', 'attributes' => ['checkout_url' => 'https://checkout.paymongo.com/test']],
    ])]);
    app(PayMongoService::class)->createCheckoutSession($f['order'], 'https://shop.test/success', 'https://shop.test/cancel');
    Http::assertSent(fn ($r) => $r->hasHeader('Idempotency-Key', 'checkout-order-'.$f['order']->order_id)
        && $r['data']['attributes']['billing'] === [
            'name' => 'Snapshot Recipient', 'email' => $f['user']->email, 'phone' => '09171234567',
            'address' => ['line1' => '123 Snapshot Street', 'line2' => 'Snapshot Barangay', 'city' => 'Quezon City',
                'state' => 'Metro Manila', 'postal_code' => '1100', 'country' => 'PH'],
        ]);
});

it('saves the payment ID and deducts stock once across duplicate signed paid events', function () {
    $f = lifecycleOrder();
    lifecycleWebhook($this, 'checkout_session.payment.paid', lifecycleSession(true))->assertOk();
    lifecycleWebhook($this, 'checkout_session.payment.paid', lifecycleSession(true))->assertOk();
    expect($f['payment']->fresh()->paymongo_payment_id)->toBe('pay_fixture');
    expect($f['order']->fresh()->status)->toBe(OrderStatus::Paid);
    expect(DB::table('stocks')->value('remaining_quantity'))->toBe(8);
    $this->assertDatabaseCount('stock_movements', 1);
    Http::assertNothingSent();
});

it('expires an unpaid session and cancels without changing stock', function () {
    $f = lifecycleOrder();
    Http::fake([
        'api.paymongo.com/v1/checkout_sessions/cs_fixture' => Http::sequence()
            ->push(['data' => lifecycleSession()])->push(['data' => lifecycleSession(false, 'expired')]),
        'api.paymongo.com/v1/checkout_sessions/cs_fixture/expire' => Http::response(['data' => lifecycleSession(false, 'expired')]),
    ]);
    app(OrderService::class)->cancel($f['order']);
    app(OrderService::class)->cancel($f['order']); // stale route-bound object
    expect($f['order']->fresh()->status)->toBe(OrderStatus::Cancelled);
    expect($f['payment']->fresh()->status)->toBe('cancelled');
    expect(DB::table('stocks')->value('remaining_quantity'))->toBe(10);
    $this->assertDatabaseCount('stock_movements', 0);
    Http::assertSentCount(3);
});

it('does not cancel locally if unpaid session expiry cannot be verified', function () {
    $f = lifecycleOrder();
    Http::fake(['api.paymongo.com/v1/checkout_sessions/cs_fixture' => Http::response([], 503)]);
    expect(fn () => app(OrderService::class)->cancel($f['order']))->toThrow(RequestException::class);
    expect($f['order']->fresh()->status)->toBe(OrderStatus::Pending);
    expect(DB::table('stocks')->value('remaining_quantity'))->toBe(10);
});

it('refunds a paid cancellation once and restores stock once even with stale duplicate requests', function () {
    $f = lifecycleOrder();
    lifecycleConfirm();
    Http::fake(['api.paymongo.com/v1/refunds' => Http::response(['data' => lifecycleRefund()])]);
    app(OrderService::class)->cancel($f['order']);
    app(OrderService::class)->cancel($f['order']);
    lifecycleConfirm();
    expect($f['order']->fresh()->status)->toBe(OrderStatus::Cancelled);
    expect($f['payment']->fresh()->status)->toBe('completed');
    expect($f['payment']->fresh()->refund_status)->toBe('pending');
    expect($f['payment']->fresh()->paymongo_refund_id)->toBe('ref_fixture');
    expect(DB::table('stocks')->value('remaining_quantity'))->toBe(10);
    $this->assertDatabaseCount('stock_movements', 2);
    Http::assertSentCount(1);
    Http::assertSent(fn ($r) => $r['data']['attributes']['payment_id'] === 'pay_fixture'
        && $r['data']['attributes']['amount'] === 100000
        && $r->header('Idempotency-Key')[0] === $f['payment']->fresh()->refund_request_key);
});

it('recovers a legacy payment ID from its session before refunding', function () {
    $f = lifecycleOrder();
    lifecycleConfirm();
    $f['payment']->update(['paymongo_payment_id' => null]);
    Http::fake([
        'api.paymongo.com/v1/checkout_sessions/cs_fixture' => Http::response(['data' => lifecycleSession(true)]),
        'api.paymongo.com/v1/refunds' => Http::response(['data' => lifecycleRefund('succeeded')]),
    ]);
    app(OrderService::class)->cancel($f['order']);
    expect($f['payment']->fresh()->paymongo_payment_id)->toBe('pay_fixture');
    expect($f['payment']->fresh()->refund_status)->toBe('refunded');
});

it('refunds a payment that wins the unpaid cancellation race without deducting stock', function () {
    $f = lifecycleOrder();
    Http::fake([
        'api.paymongo.com/v1/checkout_sessions/cs_fixture' => Http::response(['data' => lifecycleSession(true)]),
        'api.paymongo.com/v1/refunds' => Http::response(['data' => lifecycleRefund()]),
    ]);
    app(OrderService::class)->cancel($f['order']);
    lifecycleWebhook($this, 'checkout_session.payment.paid', lifecycleSession(true))->assertOk();
    expect($f['order']->fresh()->status)->toBe(OrderStatus::Cancelled);
    expect($f['payment']->fresh()->refund_status)->toBe('pending');
    $this->assertDatabaseCount('stock_movements', 0);
    Http::assertSentCount(2);
});

it('refunds a late paid event for an already cancelled order without reviving it', function () {
    $f = lifecycleOrder();
    $f['order']->update(['status' => 'cancelled']);
    $f['payment']->update(['status' => 'cancelled']);
    Http::fake(['api.paymongo.com/v1/refunds' => Http::response(['data' => lifecycleRefund()])]);
    lifecycleWebhook($this, 'checkout_session.payment.paid', lifecycleSession(true))->assertOk();
    lifecycleWebhook($this, 'checkout_session.payment.paid', lifecycleSession(true))->assertOk();
    expect($f['order']->fresh()->status)->toBe(OrderStatus::Cancelled);
    expect($f['payment']->fresh()->refund_status)->toBe('pending');
    expect(DB::table('stocks')->value('remaining_quantity'))->toBe(10);
    Http::assertSentCount(1);
});

it('uses the same durable refund key after an uncertain network failure', function () {
    $f = lifecycleOrder();
    lifecycleConfirm();
    $keys = [];
    Http::fake(['api.paymongo.com/v1/refunds' => function ($r) use (&$keys) {
        $keys[] = $r->header('Idempotency-Key')[0];
        if (count($keys) === 1) {
            throw new ConnectionException('Timed out');
        }

        return Http::response(['data' => lifecycleRefund('succeeded')]);
    }]);
    expect(fn () => app(OrderService::class)->cancel($f['order']))->toThrow(RuntimeException::class);
    expect($f['order']->fresh()->status)->toBe(OrderStatus::Cancelled);
    expect($f['payment']->fresh()->refund_status)->toBe('pending');
    expect($f['payment']->fresh()->can_retry_refund)->toBeTrue();
    app(OrderService::class)->cancel($f['order']);
    expect($keys)->toHaveCount(2);
    expect($keys[0])->toBe($keys[1]);
    expect($f['payment']->fresh()->refund_status)->toBe('refunded');
    $this->assertDatabaseCount('stock_movements', 2);
});

it('does not blindly resubmit an ambiguous refund after provider idempotency expiry', function () {
    $f = lifecycleOrder();
    lifecycleConfirm();
    Http::fake(['api.paymongo.com/v1/refunds' => Http::response([], 503)]);
    expect(fn () => app(OrderService::class)->cancel($f['order']))->toThrow(RuntimeException::class);
    $f['payment']->refresh()->update(['refund_requested_at' => now()->subHours(25)]);
    app(OrderService::class)->cancel($f['order']);
    Http::assertSentCount(1);
    expect($f['payment']->fresh()->refund_status)->toBe('pending');
    expect($f['payment']->fresh()->can_retry_refund)->toBeFalse();
});

it('records a rejected refund as failed without repeated requests or stock changes', function () {
    $f = lifecycleOrder();
    lifecycleConfirm();
    Http::fake(['api.paymongo.com/v1/refunds' => Http::response(['errors' => [['code' => 'payment_not_refundable']]], 422)]);
    app(OrderService::class)->cancel($f['order']);
    app(OrderService::class)->cancel($f['order']);
    expect($f['payment']->fresh()->refund_status)->toBe('failed');
    expect($f['payment']->fresh()->can_retry_refund)->toBeFalse();
    Http::assertSentCount(1);
    $this->assertDatabaseCount('stock_movements', 2);
});

it('handles refund webhooks idempotently and ignores older state after success', function () {
    $f = lifecycleOrder();
    lifecycleConfirm();
    Http::fake(['api.paymongo.com/v1/refunds' => Http::response(['data' => lifecycleRefund()])]);
    app(OrderService::class)->cancel($f['order']);
    lifecycleWebhook($this, 'payment.refund.updated', lifecycleRefund('processing', 150))->assertOk();
    lifecycleWebhook($this, 'payment.refunded', lifecycleRefund('succeeded', 200))->assertOk();
    lifecycleWebhook($this, 'payment.refunded', lifecycleRefund('succeeded', 200))->assertOk();
    lifecycleWebhook($this, 'payment.refund.updated', lifecycleRefund('failed', 120))->assertOk();
    expect($f['payment']->fresh()->refund_status)->toBe('refunded');
    expect($f['order']->fresh()->status)->toBe(OrderStatus::Cancelled);
    $this->assertDatabaseCount('stock_movements', 2);
});

it('handles a payment resource containing its refund and keeps partial refunds distinct', function () {
    $f = lifecycleOrder();
    lifecycleConfirm();
    $resource = lifecyclePaidResource();
    $resource['attributes']['refunds'] = [lifecycleRefund('succeeded', 200)];
    lifecycleWebhook($this, 'payment.refunded', $resource)->assertOk();
    expect($f['payment']->fresh()->refund_status)->toBe('refunded');
    $partial = lifecycleRefund('succeeded', 300);
    $partial['attributes']['amount'] = 500;
    lifecycleWebhook($this, 'payment.refunded', $partial)->assertOk();
    expect($f['payment']->fresh()->refund_amount)->toBe(100000);
});

it('tracks an asynchronous failed refund and blocks fulfillment', function () {
    $f = lifecycleOrder();
    lifecycleConfirm();
    Http::fake(['api.paymongo.com/v1/refunds' => Http::response(['data' => lifecycleRefund()])]);
    app(OrderService::class)->cancel($f['order']);
    lifecycleWebhook($this, 'payment.refund.updated', lifecycleRefund('failed', 200))->assertOk();
    expect($f['payment']->fresh()->refund_label)->toBe('Refund failed');
    expect(fn () => app(OrderService::class)->updateStatus($f['order'], OrderStatus::Shipped))
        ->toThrow(InvalidOrderTransitionException::class);
});

it('returns retryable failure for unknown payments and stock errors instead of acknowledging success', function () {
    lifecycleWebhook($this, 'checkout_session.payment.paid', lifecycleSession(true))->assertStatus(503);
    $f = lifecycleOrder();
    DB::table('stocks')->update(['remaining_quantity' => 0]);
    lifecycleWebhook($this, 'checkout_session.payment.paid', lifecycleSession(true))->assertStatus(503);
    expect($f['order']->fresh()->status)->toBe(OrderStatus::Pending);
    expect($f['payment']->fresh()->status)->toBe('pending');
    $this->assertDatabaseCount('stock_movements', 0);
});

it('returns retryable failure for refund network errors and resumes on redelivery', function () {
    $f = lifecycleOrder();
    $f['order']->update(['status' => 'cancelled']);
    Http::fake(['api.paymongo.com/v1/refunds' => Http::sequence()
        ->push([], 503)->push(['data' => lifecycleRefund('succeeded')])]);
    lifecycleWebhook($this, 'checkout_session.payment.paid', lifecycleSession(true))->assertStatus(503);
    lifecycleWebhook($this, 'checkout_session.payment.paid', lifecycleSession(true))->assertOk();
    expect($f['order']->fresh()->status)->toBe(OrderStatus::Cancelled);
    expect($f['payment']->fresh()->refund_status)->toBe('refunded');
    $this->assertDatabaseCount('stock_movements', 0);
});

it('rejects invalid signatures without touching order state', function () {
    $f = lifecycleOrder();
    $this->postJson('/webhooks/paymongo', ['data' => []])->assertStatus(401);
    expect($f['order']->fresh()->status)->toBe(OrderStatus::Pending);
});

it('keeps completed POS sales isolated from PayMongo and cancellation', function () {
    $f = lifecycleOrder();
    $cashier = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $pos = app(OrderService::class)->createPosSale([
        ['product_variant_id' => $f['variant'], 'quantity' => 2],
    ], $cashier, (string) Str::uuid());
    expect($pos->street)->toBe('In-Store Purchase');
    expect($pos->payment_method)->toBe('cash_pos');
    expect($pos->status)->toBe(OrderStatus::Completed);
    expect(fn () => app(OrderService::class)->cancel($pos))
        ->toThrow(OrderNotCancellableException::class);
    expect($pos->fresh()->status)->toBe(OrderStatus::Completed);
    expect($pos->fresh()->payment->status)->toBe('completed');
    expect(DB::table('stocks')->value('remaining_quantity'))->toBe(8);
    Http::assertNothingSent();
    $this->assertDatabaseCount('stock_movements', 1);
});

it('renders refund state for customer and admin and removes the ordinary cancellation action', function () {
    $f = lifecycleOrder();
    lifecycleConfirm();
    Http::fake(['api.paymongo.com/v1/refunds' => Http::response(['data' => lifecycleRefund()])]);
    app(OrderService::class)->cancel($f['order']);
    $this->actingAs($f['user'])->get('/orders/'.$f['order']->order_id)
        ->assertOk()->assertSee('Refund pending')->assertDontSee('Cancel Order');
    $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
    $this->actingAs($admin)->get('/admin/orders/'.$f['order']->order_id)
        ->assertOk()->assertSee('Refund pending')->assertDontSee('Ship Order')->assertDontSee('Cancel Order');
});

it('does not let the GET cancel return URL issue a refund', function () {
    $f = lifecycleOrder();
    lifecycleConfirm();
    $this->actingAs($f['user'])->get('/checkout/'.$f['order']->order_id.'/cancel')
        ->assertRedirect(route('orders.show', $f['order']->order_id));
    expect($f['order']->fresh()->status)->toBe(OrderStatus::Paid);
    Http::assertNothingSent();
});
it('closes a checkout session created after the customer already cancelled the pending order', function () {
    $f = lifecycleOrder();
    // Use the fixture's stock and product for a fresh online checkout.
    $address = $f['user']->addresses()->create([
        'full_name' => 'Saved Recipient', 'phone_number' => '09171234567',
        'street' => 'Saved Street', 'barangay' => 'Saved Barangay',
        'city' => 'Saved City', 'province' => 'Saved Province', 'postal_code' => '1100',
    ]);
    $cart = DB::table('carts')->insertGetId(['user_id' => $f['user']->id, 'status' => 0], 'cart_id');
    DB::table('cart_items')->insert(['cart_id' => $cart, 'product_variant_id' => $f['variant'], 'quantity' => 2, 'price' => 500]);
    Http::fake([
        'api.paymongo.com/v1/checkout_sessions' => function ($request) {
            $order = Order::findOrFail($request['data']['attributes']['reference_number']);
            app(OrderService::class)->cancel($order); // session request still in flight

            return Http::response(['data' => ['id' => 'cs_race',
                'attributes' => ['checkout_url' => 'https://checkout.paymongo.com/cs_race']]]);
        },
        'api.paymongo.com/v1/checkout_sessions/cs_race' => Http::response([
            'data' => ['id' => 'cs_race', 'attributes' => ['status' => 'expired', 'payments' => []]],
        ]),
    ]);
    $response = $this->actingAs($f['user'])->post('/checkout/place-order', ['address_id' => $address->address_id]);
    $order = Order::orderByDesc('order_id')->first();
    $response->assertRedirect(route('orders.show', $order->order_id));
    expect($order->status)->toBe(OrderStatus::Cancelled);
    expect($order->payment->status)->toBe('cancelled');
    expect($order->street)->toBe('Saved Street');
    Http::assertSent(fn ($r) => $r->url() === 'https://api.paymongo.com/v1/checkout_sessions'
        && $r['data']['attributes']['billing']['address']['line1'] === 'Saved Street');
    $this->assertDatabaseCount('stock_movements', 0);
});

it('rejects a mismatched payment amount without recording payment or deducting stock', function () {
    $f = lifecycleOrder();
    $session = lifecycleSession(true);
    $session['attributes']['payments'][0]['attributes']['amount'] = 1;
    lifecycleWebhook($this, 'checkout_session.payment.paid', $session)->assertStatus(503);
    expect($f['payment']->fresh()->status)->toBe('pending');
    $this->assertDatabaseCount('stock_movements', 0);
});

it('does not downgrade a completed payment when a failed event arrives later', function () {
    $f = lifecycleOrder();
    lifecycleConfirm();
    lifecycleWebhook($this, 'checkout_session.payment.failed', lifecycleSession())->assertOk();
    expect($f['payment']->fresh()->status)->toBe('completed');
    expect($f['order']->fresh()->status)->toBe(OrderStatus::Paid);
});

it('requires verification when a paid online order has no payment record', function () {
    $f = lifecycleOrder();
    $f['payment']->delete();
    $f['order']->update(['status' => 'paid']);
    expect(fn () => app(OrderService::class)->cancel($f['order']))
        ->toThrow(OrderNotCancellableException::class);
    expect($f['order']->fresh()->status)->toBe(OrderStatus::Paid);
    Http::assertNothingSent();
});

it('shows a retry for an uncertain refund and clears it after confirmation', function () {
    $f = lifecycleOrder();
    lifecycleConfirm();
    Http::fake(['api.paymongo.com/v1/refunds' => Http::sequence()
        ->push([], 503)->push(['data' => lifecycleRefund('succeeded')])]);
    $url = '/orders/'.$f['order']->order_id.'/cancel';
    $this->actingAs($f['user'])->from('/orders/'.$f['order']->order_id)->put($url)
        ->assertRedirect()->assertSessionHas('error');
    $this->get('/orders/'.$f['order']->order_id)->assertOk()
        ->assertSee('Refund pending')->assertSee('Retry refund confirmation');
    $this->put($url)->assertRedirect()->assertSessionHas('success');
    $this->get('/orders/'.$f['order']->order_id)->assertOk()
        ->assertSee('Refunded')->assertDontSee('Retry refund confirmation');
});

it('leaves completed POS address placeholders unchanged when rendering order views', function () {
    $f = lifecycleOrder();
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $pos = app(OrderService::class)->createPosSale([
        ['product_variant_id' => $f['variant'], 'quantity' => 1],
    ], $admin, (string) Str::uuid());
    $this->actingAs($admin)->get('/admin/orders/'.$pos->order_id)->assertOk()
        ->assertDontSee('Refund pending')->assertDontSee('Retry refund confirmation');
    expect($pos->fresh()->city)->toBe('N/A');
    expect($pos->fresh()->status)->toBe(OrderStatus::Completed);
    Http::assertNothingSent();
});
it('records documented payment.failed without changing order or stock', function () {
    $f = lifecycleOrder();
    $f['payment']->update(['paymongo_payment_intent_id' => 'pi_fixture']);
    Http::fake(['api.paymongo.com/v1/checkout_sessions/cs_fixture' => Http::response(['data' => lifecycleSession()])]);
    $failed = ['id' => 'pay_failed', 'attributes' => ['status' => 'failed', 'payment_intent_id' => 'pi_fixture']];
    lifecycleWebhook($this, 'payment.failed', $failed)->assertOk();
    lifecycleWebhook($this, 'payment.failed', $failed)->assertOk();
    expect($f['payment']->fresh()->status)->toBe('failed');
    expect($f['payment']->fresh()->paymongo_payment_id)->toBeNull();
    expect($f['order']->fresh()->status)->toBe(OrderStatus::Pending);
    $this->assertDatabaseCount('stock_movements', 0);
});

it('shows expired payment and retry when no failure webhook was delivered', function () {
    $f = lifecycleOrder();
    Http::fake(['api.paymongo.com/v1/checkout_sessions/cs_fixture' => Http::response([
        'data' => lifecycleSession(false, 'expired'),
    ])]);
    $this->actingAs($f['user'])->get('/orders/'.$f['order']->order_id)->assertOk()
        ->assertSee('Expired')->assertSee('Retry Payment');
    expect($f['order']->fresh()->status)->toBe(OrderStatus::Pending);
    expect($f['payment']->fresh()->status)->toBe('expired');
    $this->assertDatabaseCount('stock_movements', 0);
});

it('recognizes an expired-source attempt from the retrieved intent error', function () {
    $f = lifecycleOrder();
    $session = lifecycleSession();
    $session['attributes']['payment_intent']['attributes']['last_payment_error'] = [
        'failed_message' => 'Source src_fixture has expired status',
    ];
    Http::fake(['api.paymongo.com/v1/checkout_sessions/cs_fixture' => Http::response(['data' => $session])]);
    app(OrderService::class)->refreshCheckoutPayment($f['order']);
    expect($f['payment']->fresh()->status)->toBe('failed');
    expect($f['order']->fresh()->status)->toBe(OrderStatus::Pending);
    $this->assertDatabaseCount('stock_movements', 0);
});

it('creates a fresh checkout for the same order and ignores a repeated retry form', function () {
    $f = lifecycleOrder();
    $f['payment']->update(['status' => 'failed']);
    Http::fake([
        'api.paymongo.com/v1/checkout_sessions/cs_fixture' => Http::sequence()
            ->push(['data' => lifecycleSession()])->push(['data' => lifecycleSession(false, 'expired')]),
        'api.paymongo.com/v1/checkout_sessions/cs_fixture/expire' => Http::response([]),
        'api.paymongo.com/v1/checkout_sessions' => Http::response(['data' => [
            'id' => 'cs_retry', 'attributes' => [
                'checkout_url' => 'https://checkout.paymongo.com/retry', 'payment_intent' => ['id' => 'pi_retry'],
            ],
        ]]),
    ]);
    $url = '/orders/'.$f['order']->order_id.'/retry-payment';
    $this->actingAs($f['user'])->post($url, ['checkout_session_id' => 'cs_fixture'])
        ->assertRedirect('https://checkout.paymongo.com/retry');
    $this->post($url, ['checkout_session_id' => 'cs_fixture'])->assertRedirect('/orders/'.$f['order']->order_id);
    expect($f['payment']->fresh()->checkout_session_id)->toBe('cs_retry');
    expect($f['payment']->fresh()->paymongo_payment_intent_id)->toBe('pi_retry');
    expect($f['payment']->fresh()->previous_checkout_session_ids)->toBe(['cs_fixture']);
    expect($f['payment']->fresh()->status)->toBe('pending');
    Http::assertSentCount(4);
    Http::assertSent(function ($request) {
        if ($request->method() !== 'POST' || $request->url() !== 'https://api.paymongo.com/v1/checkout_sessions') {
            return false;
        }
        $attributes = $request['data']['attributes'];

        return ! isset($attributes['source'], $attributes['payment_intent'])
            && ! array_key_exists('source', $attributes)
            && ! array_key_exists('payment_intent_id', $attributes)
            && ! array_key_exists('checkout_url', $attributes);
    });
    $this->assertDatabaseCount('orders', 1);
    $this->assertDatabaseCount('payments', 1);
    $this->assertDatabaseCount('stock_movements', 0);
    lifecycleWebhook($this, 'checkout_session.payment.failed', lifecycleSession())->assertOk();
    expect($f['payment']->fresh()->status)->toBe('pending');
    $paid = lifecycleSession(true);
    $paid['id'] = 'cs_retry';
    lifecycleWebhook($this, 'checkout_session.payment.paid', $paid)->assertOk();
    lifecycleWebhook($this, 'checkout_session.payment.paid', $paid)->assertOk();
    expect($f['order']->fresh()->status)->toBe(OrderStatus::Paid);
    expect(DB::table('stocks')->value('remaining_quantity'))->toBe(8);
    $this->assertDatabaseCount('stock_movements', 1);
});

it('reconciles payment that won the race before retry instead of creating another checkout', function () {
    $f = lifecycleOrder();
    Http::fake(['api.paymongo.com/v1/checkout_sessions/cs_fixture' => Http::response(['data' => lifecycleSession(true)])]);
    $this->actingAs($f['user'])->post('/orders/'.$f['order']->order_id.'/retry-payment',
        ['checkout_session_id' => 'cs_fixture'])->assertRedirect('/orders/'.$f['order']->order_id);
    expect($f['order']->fresh()->status)->toBe(OrderStatus::Paid);
    Http::assertSentCount(1);
    $this->assertDatabaseCount('stock_movements', 1);
});

it('does not downgrade a completed order when payment.failed arrives late', function () {
    $f = lifecycleOrder();
    $f['payment']->update(['paymongo_payment_intent_id' => 'pi_fixture']);
    lifecycleConfirm();
    lifecycleWebhook($this, 'payment.failed', ['id' => 'pay_failed', 'attributes' => [
        'status' => 'failed', 'payment_intent_id' => 'pi_fixture',
    ]])->assertOk();
    expect($f['payment']->fresh()->status)->toBe('completed');
    expect($f['order']->fresh()->status)->toBe(OrderStatus::Paid);
    $this->assertDatabaseCount('stock_movements', 1);
    Http::assertNothingSent();
});

it('records failure without provider calls while test authentication finishes', function () {
    $f = lifecycleOrder();
    $f['payment']->update(['paymongo_payment_intent_id' => 'pi_fixture']);
    Http::fake(['*' => Http::response([], 503)]);
    lifecycleWebhook($this, 'payment.failed', ['id' => 'pay_failed', 'attributes' => [
        'status' => 'failed', 'payment_intent_id' => 'pi_fixture',
        'source' => ['id' => 'src_expired', 'type' => 'gcash'],
    ]])->assertOk();
    expect($f['payment']->fresh()->status)->toBe('failed');
    expect($f['order']->fresh()->status)->toBe(OrderStatus::Pending);
    $this->assertDatabaseCount('stock_movements', 0);
    Http::assertNothingSent();
    lifecycleWebhook($this, 'checkout_session.payment.paid', lifecycleSession(true))->assertOk();
    expect($f['payment']->fresh()->status)->toBe('completed');
    $this->assertDatabaseCount('stock_movements', 1);
});

it('does not create a replacement when the old session cannot be verified', function () {
    $f = lifecycleOrder();
    Http::fake(['api.paymongo.com/v1/checkout_sessions/cs_fixture' => Http::response([], 503)]);
    $this->actingAs($f['user'])->from('/orders/'.$f['order']->order_id)
        ->post('/orders/'.$f['order']->order_id.'/retry-payment', ['checkout_session_id' => 'cs_fixture'])
        ->assertRedirect()->assertSessionHas('error');
    expect($f['payment']->fresh()->checkout_session_id)->toBe('cs_fixture');
    Http::assertSentCount(1);
    $this->assertDatabaseCount('stock_movements', 0);
});

it('rejects payment retries for another customer paid cancelled and POS orders', function () {
    $f = lifecycleOrder();
    $url = '/orders/'.$f['order']->order_id.'/retry-payment';
    $data = ['checkout_session_id' => 'cs_fixture'];
    $this->actingAs(User::factory()->create())->post($url, $data)->assertForbidden();
    foreach (['paid', 'cancelled'] as $status) {
        $f['order']->update(['status' => $status]);
        $this->actingAs($f['user'])->post($url, $data)->assertRedirect()->assertSessionHas('error');
    }
    $f['order']->update(['status' => 'pending', 'sale_type' => 'pos']);
    $this->post($url, $data)->assertRedirect()->assertSessionHas('error');
    Http::assertNothingSent();
});

it('closes a replacement before confirming a delayed paid event from the retired session', function () {
    $f = lifecycleOrder();
    $f['payment']->update(['checkout_session_id' => 'cs_retry', 'previous_checkout_session_ids' => ['cs_fixture']]);
    $replacement = lifecycleSession(false, 'expired');
    $replacement['id'] = 'cs_retry';
    Http::fake(['api.paymongo.com/v1/checkout_sessions/cs_retry' => Http::response(['data' => $replacement])]);
    lifecycleWebhook($this, 'checkout_session.payment.paid', lifecycleSession(true))->assertOk();
    lifecycleWebhook($this, 'checkout_session.payment.paid', lifecycleSession(true))->assertOk();
    expect($f['order']->fresh()->status)->toBe(OrderStatus::Paid);
    expect($f['payment']->fresh()->checkout_session_id)->toBe('cs_fixture');
    $this->assertDatabaseCount('stock_movements', 1);
    Http::assertSentCount(1);
});

it('never redirects a retry to a reused session or intent', function (string $sessionId, string $intentId) {
    $f = lifecycleOrder();
    $f['payment']->update(['status' => 'failed', 'paymongo_payment_intent_id' => 'pi_fixture']);
    Http::fake([
        'api.paymongo.com/v1/checkout_sessions/cs_fixture' => Http::response(['data' => lifecycleSession(false, 'expired')]),
        'api.paymongo.com/v1/checkout_sessions' => Http::response(['data' => [
            'id' => $sessionId, 'attributes' => ['checkout_url' => 'https://checkout.paymongo.com/reused',
                'payment_intent' => ['id' => $intentId]],
        ]]),
    ]);
    $this->actingAs($f['user'])->from('/orders/'.$f['order']->order_id)
        ->post('/orders/'.$f['order']->order_id.'/retry-payment', ['checkout_session_id' => 'cs_fixture'])
        ->assertRedirect('/orders/'.$f['order']->order_id)->assertSessionHas('error');
    expect($f['payment']->fresh()->status)->toBe('failed');
    expect($f['payment']->fresh()->checkout_session_id)->toBe('cs_fixture');
    $this->assertDatabaseCount('stock_movements', 0);
})->with([['cs_fixture', 'pi_new'], ['cs_new', 'pi_fixture']]);
