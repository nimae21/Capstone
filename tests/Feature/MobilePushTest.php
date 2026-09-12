<?php

use App\Enums\OrderStatus;
use App\Models\ApprovalRequest;
use App\Models\MobilePushDelivery;
use App\Models\MobilePushDevice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\FirebasePushSender;
use App\Services\MobilePushOutbox;
use App\Services\OrderService;
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
    // accessToken() is stubbed below, so no credential is ever fetched here:
    // the value only has to satisfy the sender's credential checks.
    config([
        'mobile_push.enabled' => true,
        'mobile_push.project_id' => 'achilles-test',
        'mobile_push.credentials_json' => json_encode(serviceAccountFixture()),
    ]);
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
            && ! str_contains($request->body(), 'Private Recipient')
            && ! str_contains($request->body(), 'Private Street');
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
    $this->app->instance(FirebasePushSender::class, new FirebasePushSender);
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

it('reaches every phone the super admin is signed in on', function () {
    [, $token] = pushLogin($this);
    $phone = pushRegister($this);
    $tablet = pushRegister($this);
    expect($tablet->id)->not->toBe($phone->id)
        ->and($tablet->personal_access_token_id)->toBe($token->id);

    pushOrder()->update(['status' => 'paid']);

    // One alert per registered device, all belonging to the same session.
    expect(MobilePushDelivery::where('kind', 'order_paid')->count())->toBe(2);
    expect(MobilePushDelivery::where('kind', 'order_paid')->pluck('device_id')->sort()->values()->all())
        ->toBe(collect([$phone->id, $tablet->id])->sort()->values()->all());
});

it('summarises a batch of admin submissions instead of buzzing once per row', function () {
    [$admin] = pushLogin($this);
    pushRegister($this);
    $requester = User::factory()->create(['role' => 'admin']);

    foreach (range(1, 3) as $index) {
        ApprovalRequest::create([
            'requester_id' => $requester->id,
            'entity_type' => 'category',
            'payload' => ['category_name' => 'Batch '.$index],
        ]);
    }

    // The in-app centre keeps every request...
    $this->assertDatabaseCount('notifications', 3);
    expect($admin->notifications()->count())->toBe(3);

    // ...but the phone is told once, and tapping opens the whole queue.
    $this->assertDatabaseCount('mobile_push_deliveries', 1);
    $delivery = MobilePushDelivery::first();
    expect($delivery->kind)->toBe('approval_submitted')
        ->and($delivery->data['title'])->toBe('3 changes awaiting approval')
        ->and($delivery->data['route'])->toBe('/tabs/approvals')
        ->and($delivery->data['meta']['digest_count'])->toBe(3)
        ->and($delivery->notification_id)->toBeNull();
});

it('never rewrites an alert that has already been sent', function () {
    pushLogin($this);
    pushRegister($this);
    $requester = User::factory()->create(['role' => 'admin']);
    $submit = fn (int $index) => ApprovalRequest::create([
        'requester_id' => $requester->id,
        'entity_type' => 'category',
        'payload' => ['category_name' => 'After '.$index],
    ]);

    $submit(1);
    $submit(2);
    expect(MobilePushDelivery::count())->toBe(1);

    MobilePushDelivery::first()->update(['status' => 'sent', 'sent_at' => now()]);
    $submit(3);

    // The delivered summary is untouched; the new request starts a fresh alert.
    expect(MobilePushDelivery::count())->toBe(2);
    expect(MobilePushDelivery::where('status', 'pending')->first()->data['title'])
        ->toBe('Approval needed');
    expect(MobilePushDelivery::where('status', 'sent')->first()->data['meta']['digest_count'])->toBe(2);
});

it('alerts the super admin when a refund completes and never twice for the same outcome', function () {
    [$admin] = pushLogin($this);
    pushRegister($this);

    $order = pushOrder('paid');
    $payment = Payment::create([
        'order_id' => $order->order_id,
        'checkout_session_id' => 'cs_refund_test',
        'paymongo_payment_id' => 'pay_refund_test',
        'method' => 'gcash',
        'status' => 'completed',
        'refund_status' => 'pending',
        'refund_amount' => 250000,
        'refund_request_key' => 'refund-key-test',
        'refund_requested_at' => now(),
    ]);
    $refund = ['id' => 'ref_refund_test', 'attributes' => [
        'payment_id' => 'pay_refund_test', 'amount' => 250000, 'status' => 'succeeded', 'updated_at' => 10,
    ]];

    app(OrderService::class)->syncRefund($refund);
    // A replayed webhook must not alert the Super Admin a second time.
    app(OrderService::class)->syncRefund($refund);

    expect($payment->fresh()->refund_status)->toBe('refunded');
    expect(MobilePushDelivery::where('kind', 'refund_completed')->count())->toBe(1);
    expect($admin->notifications()->get()->map(fn ($row) => $row->data['title'] ?? null)->all())
        ->toContain('Refund completed');
});

it('alerts the super admin when a refund fails', function () {
    pushLogin($this);
    pushRegister($this);

    $order = pushOrder('paid');
    $payment = Payment::create([
        'order_id' => $order->order_id,
        'checkout_session_id' => 'cs_refund_failed',
        'paymongo_payment_id' => 'pay_refund_failed',
        'method' => 'gcash',
        'status' => 'completed',
        'refund_status' => 'pending',
        'refund_amount' => 250000,
        'refund_request_key' => 'refund-key-failed',
        'refund_requested_at' => now(),
    ]);

    app(OrderService::class)->syncRefund(['id' => 'ref_refund_failed', 'attributes' => [
        'payment_id' => 'pay_refund_failed', 'amount' => 250000, 'status' => 'failed', 'updated_at' => 10,
    ]]);

    expect($payment->fresh()->refund_status)->toBe('failed');
    expect(MobilePushDelivery::where('kind', 'refund_failed')->count())->toBe(1);
});

it('never delivers to a device whose account is no longer an active super admin', function () {
    [$admin] = pushLogin($this);
    pushRegister($this);
    $order = pushOrder();
    $order->update(['status' => 'paid']);
    MobilePushDelivery::where('kind', 'order_placed')->update(['status' => 'skipped']);

    // Demoted after registering: the queued alert must not reach the phone.
    $admin->role = 'admin';
    $admin->save();

    Http::fake(['fcm.googleapis.com/*' => Http::response(['name' => 'projects/test/messages/1'])]);
    expect(app(MobilePushOutbox::class)->deliverPending($this->sender))->toBe(0);
    Http::assertNothingSent();
    expect(MobilePushDelivery::where('kind', 'order_paid')->first()->status)->toBe('skipped');
});
