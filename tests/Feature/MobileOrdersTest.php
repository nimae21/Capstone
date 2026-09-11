<?php

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

function mobileOrder(string $status = 'paid', string $saleType = 'online'): Order
{
    return Order::create([
        'user_id' => User::factory()->create(['role' => 'user'])->id,
        'sale_type' => $saleType, 'status' => $status, 'total_amount' => 2500,
        'full_name' => 'Delivery Recipient', 'phone_number' => '09171234567',
        'street' => '123 Test Street', 'barangay' => 'Test Barangay',
        'city' => 'Test City', 'province' => 'Test Province', 'postal_code' => '1100',
    ]);
}

it('only lets an active super admin read mobile orders', function () {
    $order = mobileOrder();

    $this->getJson('/api/orders')->assertUnauthorized();
    $this->getJson('/api/orders/'.$order->order_id)->assertUnauthorized();

    Sanctum::actingAs(User::factory()->create(['role' => 'user']));
    $this->getJson('/api/orders')->assertForbidden();
    $this->getJson('/api/orders/'.$order->order_id)->assertForbidden();

    Sanctum::actingAs(User::factory()->create(['role' => 'admin']));
    $this->getJson('/api/orders')->assertForbidden();
    $this->getJson('/api/orders/'.$order->order_id)->assertForbidden();

    Sanctum::actingAs(User::factory()->create(['role' => 'super_admin', 'is_active' => false]));
    $this->getJson('/api/orders')->assertForbidden();
});

it('does not expose any order mutation endpoint to the super admin', function () {
    $order = mobileOrder();
    Sanctum::actingAs(User::factory()->create(['role' => 'super_admin']));

    // The website has never let a Super Admin ship, complete or cancel an order,
    // so the mobile API carries no such route at all.
    $this->putJson('/api/orders/'.$order->order_id.'/status', ['status' => 'shipped'])->assertNotFound();
    $this->postJson('/api/orders/'.$order->order_id.'/confirm')->assertNotFound();
    $this->putJson('/api/orders/'.$order->order_id.'/cancel')->assertNotFound();

    expect($order->fresh()->status)->toBe(OrderStatus::Paid);
});

it('filters, searches and paginates online orders and returns recipient details', function () {
    Sanctum::actingAs(User::factory()->create(['role' => 'super_admin']));
    $paid = mobileOrder();
    mobileOrder('shipped')->update(['full_name' => 'Second Recipient']);
    mobileOrder('paid', 'pos');

    $this->getJson('/api/orders?status=paid')->assertOk()->assertJsonPath('total', 1)
        ->assertJsonPath('data.0.id', $paid->order_id)->assertJsonPath('data.0.status_key', 'paid');
    $this->getJson('/api/orders?status=all')->assertOk()->assertJsonPath('total', 2);
    $this->getJson('/api/orders?status=invalid')->assertUnprocessable();
    $this->getJson('/api/orders?search=Second')->assertOk()->assertJsonPath('total', 1);
    $this->getJson('/api/orders?search=Recipient')->assertOk()->assertJsonPath('total', 2);
    $this->getJson('/api/orders?search=nobody')->assertOk()->assertJsonPath('total', 0);
    $this->getJson('/api/orders/counts')->assertOk()
        ->assertJsonPath('all', 2)->assertJsonPath('paid', 1)->assertJsonPath('shipped', 1)->assertJsonPath('completed', 0);
});

it('returns full order detail with shipping, payment and a real status timeline', function () {
    $order = mobileOrder('pending');
    $order->update(['status' => 'paid']);
    $order->update(['status' => 'shipped']);
    $order->update(['status' => 'completed']);

    Sanctum::actingAs(User::factory()->create(['role' => 'super_admin']));
    $this->getJson('/api/orders/'.$order->order_id)
        ->assertOk()
        ->assertJsonPath('recipient', 'Delivery Recipient')
        ->assertJsonPath('shipping.city', 'Test City')
        ->assertJsonPath('shipping.phone', '09171234567')
        ->assertJsonPath('address', '123 Test Street, Test Barangay, Test City, Test Province, 1100')
        ->assertJsonPath('payment', null)
        ->assertJsonStructure(['timeline' => [['label', 'at', 'kind']]]);

    $timeline = collect($this->getJson('/api/orders/'.$order->order_id)->json('timeline'))->pluck('label');
    expect($timeline)->toContain('Order placed')
        ->toContain('Status changed to Paid')
        ->toContain('Status changed to Shipped')
        ->toContain('Status changed to Completed');
});

it('hides POS orders from the mobile order feed', function () {
    $pos = mobileOrder('completed', 'pos');
    Sanctum::actingAs(User::factory()->create(['role' => 'super_admin']));

    $this->getJson('/api/orders/' . $pos->order_id)->assertNotFound();
    $this->getJson('/api/orders?status=all')->assertOk()->assertJsonPath('total', 0);
});