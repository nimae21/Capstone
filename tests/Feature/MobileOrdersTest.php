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

it('requires an admin for mobile order access and changes', function () {
    $order = mobileOrder();
    $this->getJson('/api/orders')->assertUnauthorized();
    Sanctum::actingAs(User::factory()->create(['role' => 'user']));
    $this->getJson('/api/orders')->assertForbidden();
    $this->getJson('/api/orders/'.$order->order_id)->assertForbidden();
    $this->putJson('/api/orders/'.$order->order_id.'/status', ['status' => 'shipped'])->assertForbidden();
    expect($order->fresh()->status)->toBe(OrderStatus::Paid);
});

it('filters and paginates online orders and returns recipient details', function () {
    Sanctum::actingAs(User::factory()->create(['role' => 'admin']));
    $paid = mobileOrder();
    mobileOrder('shipped');
    mobileOrder('paid', 'pos');
    $this->getJson('/api/orders?status=paid')->assertOk()->assertJsonPath('total', 1)
        ->assertJsonPath('data.0.id', $paid->order_id)->assertJsonPath('data.0.status_key', 'paid');
    $this->getJson('/api/orders?status=all')->assertOk()->assertJsonPath('total', 2);
    $this->getJson('/api/orders/'.$paid->order_id)->assertOk()->assertJsonPath('recipient', 'Delivery Recipient');
    $this->getJson('/api/orders?status=invalid')->assertUnprocessable();
});

it('performs the same shipping and delivery transitions as the website', function () {
    Sanctum::actingAs(User::factory()->create(['role' => 'admin']));
    $order = mobileOrder();
    $url = '/api/orders/'.$order->order_id.'/status';
    $this->putJson($url, ['status' => 'shipped'])->assertOk()->assertJsonPath('order.status_key', 'shipped');
    expect($order->fresh()->status)->toBe(OrderStatus::Shipped);
    $this->putJson($url, ['status' => 'shipped'])->assertUnprocessable();
    $this->putJson($url, ['status' => 'completed'])->assertOk()->assertJsonPath('order.status_key', 'completed');
    expect($order->fresh()->status)->toBe(OrderStatus::Completed);
    $this->putJson($url, ['status' => 'shipped'])->assertUnprocessable();
});

it('cannot ship unpaid orders or manually confirm payment or change POS orders', function () {
    Sanctum::actingAs(User::factory()->create(['role' => 'admin']));
    $unpaid = mobileOrder('pending');
    $this->putJson('/api/orders/'.$unpaid->order_id.'/status', ['status' => 'shipped'])->assertUnprocessable();
    $this->putJson('/api/orders/'.$unpaid->order_id.'/status', ['status' => 'paid'])->assertUnprocessable();
    $this->putJson('/api/orders/'.$unpaid->order_id.'/status', ['status' => 'cancelled'])->assertUnprocessable();
    $pos = mobileOrder('paid', 'pos');
    $this->putJson('/api/orders/'.$pos->order_id.'/status', ['status' => 'shipped'])->assertNotFound();
    expect($unpaid->fresh()->status)->toBe(OrderStatus::Pending);
    expect($pos->fresh()->status)->toBe(OrderStatus::Paid);
});

it('keeps the existing confirm endpoint working', function () {
    Sanctum::actingAs(User::factory()->create(['role' => 'admin']));
    $order = mobileOrder();
    $this->postJson('/api/orders/'.$order->order_id.'/confirm')->assertOk();
    expect($order->fresh()->status)->toBe(OrderStatus::Shipped);
});