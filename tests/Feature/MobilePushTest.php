<?php

use App\Enums\OrderStatus;
use App\Models\MobilePushDelivery;
use App\Models\MobilePushDevice;
use App\Models\Order;
use App\Models\User;
use App\Services\FirebasePushSender;
use App\Services\MobilePushOutbox;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

function pushLogin($test, ?User $user = null): array
{
    $user ??= User::factory()->create(['role' => 'super_admin', 'is_active' => true]);
    $token = $user->createToken('mobile-app');
    app('auth')->forgetGuards();
    $test->withToken($token->plainTextToken);
    return [$user, $token->accessToken];
}

function pushRegister($test): MobilePushDevice
{
    $id = (string) Str::uuid();
    $test->putJson('/api/push/device', ['installation_id' => $id, 'token' => 'test-fcm-token-'.Str::random(30)])->assertOk();
    return MobilePushDevice::where('installation_id', $id)->firstOrFail();
}

function pushOrder(string $status = 'pending', string $saleType = 'online'): Order
{
    return Order::create([
        'user_id' => User::factory()->create(['role' => 'user'])->id,
        'sale_type' => $saleType, 'status' => $status, 'total_amount' => 2500,
        'full_name' => 'Private Recipient', 'phone_number' => '09171234567',
        'street' => 'Private Street', 'barangay' => 'Test', 'city' => 'Test', 'province' => 'Test', 'postal_code' => '1100',
    ]);
}

beforeEach(function () {
    Http::preventStrayRequests();
    Cache::flush();
    config(['mobile_push.enabled' => true, 'mobile_push.project_id' => 'achilles-test', 'mobile_push.credentials' => 'test-key.json']);
    $this->sender = Mockery::mock(FirebasePushSender::class)->makePartial()->shouldAllowMockingProtectedMethods();
    $this->sender->shouldReceive('configured')->andReturn(true);
    $this->sender->shouldReceive('accessToken')->andReturn('fake-oauth-token');
    $this->app->instance(FirebasePushSender::class, $this->sender);
});

it('only lets an active super admin register a phone', function () {
    $input = ['installation_id' => (string) Str::uuid(), 'token' => str_repeat('a', 40)];
    $this->putJson('/api/push/device', $input)->assertUnauthorized();
    pushLogin($this, User::factory()->create(['role' => 'user']));
    $this->putJson('/api/push/device', $input)->assertForbidden();
    pushLogin($this, User::factory()->create(['role' => 'admin']));
    $this->putJson('/api/push/device', $input)->assertForbidden();
    pushLogin($this, User::factory()->create(['role' => 'super_admin', 'is_active' => false]));
    $this->putJson('/api/push/device', $input)->assertForbidden();
    $this->assertDatabaseCount('mobile_push_devices', 0);
});

it('encrypts tokens and refreshes a phone without duplicating it', function () {
    pushLogin($this);
    $device = pushRegister($this);
    $newToken = str_repeat('b', 50);
    $this->putJson('/api/push/device', ['installation_id' => $device->installation_id, 'token' => $newToken])->assertOk();
    $this->assertDatabaseCount('mobile_push_devices', 1);
    expect($device->fresh()->token)->toBe($newToken);
    expect(DB::table('mobile_push_devices')->value('token'))->not->toBe($newToken);
    $this->getJson('/api/push/status?installation_id='.$device->installation_id)
        ->assertOk()->assertJsonPath('registered', true)->assertJsonPath('worker_running', false);
});

it('revokes only the current session phone registrations on logout', function () {
    [$admin] = pushLogin($this);
    $first = pushRegister($this);
    pushLogin($this, $admin);
    $second = pushRegister($this);
    $this->postJson('/api/logout')->assertOk();
    expect(MobilePushDevice::find($first->id))->not->toBeNull();
    expect(MobilePushDevice::find($second->id))->toBeNull();
});

it('cannot disable or test a different session phone', function () {
    [$admin] = pushLogin($this);
    $first = pushRegister($this);
    pushLogin($this, $admin);
    $this->deleteJson('/api/push/device', ['installation_id' => $first->installation_id])->assertOk();
    expect($first->fresh())->not->toBeNull();
    $this->postJson('/api/push/test', ['installation_id' => $first->installation_id])->assertNotFound();
});

it('clears old pending alerts when a phone changes sessions', function () {
    [$admin] = pushLogin($this);
    $device = pushRegister($this);
    pushOrder()->update(['status' => 'paid']);
    expect(MobilePushDelivery::count())->toBeGreaterThan(0);
    pushLogin($this, $admin);
    $this->putJson('/api/push/device', ['installation_id' => $device->installation_id, 'token' => $device->token])->assertOk();
    $this->assertDatabaseCount('mobile_push_deliveries', 0);
});

it('stores a notification and stages a phone alert for a new and a paid order', function () {
    [$admin] = pushLogin($this);
    pushRegister($this);

    $order = pushOrder();
    expect($order->fresh()->status)->toBe(OrderStatus::Pending);
    $this->assertDatabaseCount('notifications', 1);
    expect(MobilePushDelivery::where('kind', 'order_placed')->count())->toBe(1);

    $order->update(['status' => 'paid']);
    $this->assertDatabaseCount('notifications', 2);
    expect(MobilePushDelivery::where('kind', 'order_paid')->count())->toBe(1);

    // Non-status changes and POS sales must not alert anyone.
    $order->update(['full_name' => 'Another Recipient']);
    pushOrder('completed', 'pos');
    $this->assertDatabaseCount('notifications', 2);
    expect(MobilePushDelivery::count())->toBe(2);

    expect($admin->fresh()->unreadNotifications()->count())->toBe(2);
    Http::assertNothingSent();
});

it('rolls back notification and alert creation together with a failed payment transaction', function () {
    pushLogin($this);
    pushRegister($this);
    $order = pushOrder();
    $this->assertDatabaseCount('notifications', 1);

    DB::beginTransaction();
    $order->update(['status' => 'paid']);
    $this->assertDatabaseCount('notifications', 2);
    expect(MobilePushDelivery::where('kind', 'order_paid')->count())->toBe(1);
    DB::rollBack();

    $this->assertDatabaseCount('notifications', 1);
    $this->assertDatabaseCount('mobile_push_deliveries', 1);
    expect($order->fresh()->status)->toBe(OrderStatus::Pending);
});

it('sends a paid-order alert once with a safe order link and no customer details', function () {
    pushLogin($this);
    $device = pushRegister($this);
    $order = pushOrder();
    $order->update(['status' => 'paid']);
    MobilePushDelivery::where('kind', 'order_placed')->update(['status' => 'skipped']);

    Http::fake(['fcm.googleapis.com/*' => Http::response(['name' => 'projects/test/messages/1'])]);
    $outbox = app(MobilePushOutbox::class);
    expect($outbox->deliverPending($this->sender))->toBe(1);
    expect($outbox->deliverPending($this->sender))->toBe(0);
    Http::assertSent(function ($request) use ($order, $device) {
        return $request['message']['data']['order_id'] === (string) $order->order_id
            && $request['message']['token'] === $device->token
            && $request['message']['android']['notification']['channel_id'] === 'orders'
            && $request['message']['data']['route'] === '/tabs/orders/'.$order->order_id
            && !str_contains($request->body(), 'Private Recipient')
            && !str_contains($request->body(), 'Private Street');
    });
    Http::assertSentCount(1);
    expect(MobilePushDelivery::where('kind', 'order_paid')->first()->status)->toBe('sent');
});

it('retries temporary provider failures without delaying payment', function () {
    pushLogin($this);
    pushRegister($this);
    $order = pushOrder();
    $order->update(['status' => 'paid']);
    MobilePushDelivery::where('kind', 'order_placed')->update(['status' => 'skipped']);
    Http::fake(['fcm.googleapis.com/*' => Http::sequence()->push([], 503)->push(['name' => 'ok'])]);
    $outbox = app(MobilePushOutbox::class);
    expect($outbox->deliverPending($this->sender))->toBe(0);
    expect(MobilePushDelivery::where('kind', 'order_paid')->first()->status)->toBe('pending');
    expect($order->fresh()->status)->toBe(OrderStatus::Paid);
    expect($outbox->deliverPending($this->sender))->toBe(0);
    $this->travel(61)->seconds();
    expect($outbox->deliverPending($this->sender))->toBe(1);
});

it('retires invalid FCM tokens instead of retrying them forever', function () {
    pushLogin($this);
    $device = pushRegister($this);
    pushOrder()->update(['status' => 'paid']);
    Http::fake(['fcm.googleapis.com/*' => Http::response(['error' => ['details' => [['errorCode' => 'UNREGISTERED']]]], 404)]);
    app(MobilePushOutbox::class)->deliverPending($this->sender);
    expect($device->fresh()->enabled)->toBeFalse();
    expect(MobilePushDelivery::where('status', 'failed')->count())->toBeGreaterThan(0);
});

it('skips expired sessions and orders already handled on the website', function () {
    [, $token] = pushLogin($this);
    pushRegister($this);
    $order = pushOrder();
    $order->update(['status' => 'paid']);
    $token->update(['expires_at' => now()->subMinute()]);
    app(MobilePushOutbox::class)->deliverPending($this->sender);
    expect(MobilePushDelivery::where('kind', 'order_paid')->first()->status)->toBe('skipped');

    pushLogin($this);
    pushRegister($this);
    $other = pushOrder();
    $other->update(['status' => 'paid']);
    $other->update(['status' => 'shipped']);
    MobilePushDelivery::where('status', 'pending')->update(['status' => 'skipped']);
    app(MobilePushOutbox::class)->deliverPending($this->sender);
    Http::assertNothingSent();
    expect(MobilePushDelivery::where('status', 'pending')->count())->toBe(0);
});

it('queues a test only for the requesting phone', function () {
    pushLogin($this);
    $device = pushRegister($this);
    $this->postJson('/api/push/test', ['installation_id' => $device->installation_id])->assertStatus(202);
    expect(MobilePushDelivery::first()->kind)->toBe('test');
    expect(MobilePushDelivery::first()->device_id)->toBe($device->id);
});

it('reports unconfigured Firebase honestly and refuses registration', function () {
    pushLogin($this);
    $this->app->instance(FirebasePushSender::class, new FirebasePushSender());
    config(['mobile_push.enabled' => false]);
    $this->getJson('/api/push/status')->assertOk()->assertJsonPath('configured', false);
    $this->putJson('/api/push/device', ['installation_id' => (string) Str::uuid(), 'token' => str_repeat('a', 40)])->assertStatus(503);
});

it('serves the in-app notification centre with unread counts and read state', function () {
    [$admin] = pushLogin($this);
    pushRegister($this);
    $order = pushOrder();
    $order->update(['status' => 'paid']);

    $this->getJson('/api/notifications/unread-count')->assertOk()->assertJsonPath('unread', 2);

    $list = $this->getJson('/api/notifications')->assertOk()->assertJsonPath('total', 2);
    $rows = collect($list->json('data'));
    $paid = $rows->firstWhere('title', 'Order #'.$order->order_id.' paid');
    expect($paid)->not->toBeNull()
        ->and($paid['read'])->toBeFalse()
        ->and($paid['route'])->toBe('/tabs/orders/'.$order->order_id);
    expect($rows->pluck('title'))->toContain('New order placed');

    $this->postJson('/api/notifications/'.$paid['id'].'/read')->assertOk()->assertJsonPath('unread', 1);
    $this->getJson('/api/notifications?filter=unread')->assertOk()->assertJsonPath('total', 1);
    $this->postJson('/api/notifications/read-all')->assertOk()->assertJsonPath('unread', 0);
    expect($admin->fresh()->unreadNotifications()->count())->toBe(0);
});

it('does not let one super admin read another account notification', function () {
    [$first] = pushLogin($this);
    pushRegister($this);
    pushOrder()->update(['status' => 'paid']);
    $id = $first->notifications()->first()->id;

    pushLogin($this);
    $this->postJson('/api/notifications/'.$id.'/read')->assertNotFound();
});