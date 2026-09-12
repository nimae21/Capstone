<?php

use App\Models\ActivityLog;
use App\Models\AdminInvitation;
use App\Models\ApprovalRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\ApprovalService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;

function superAdminUser(bool $active = true): User
{
    return User::factory()->create(['role' => 'super_admin', 'is_active' => $active]);
}

function superAdminOrder(string $status = 'paid'): Order
{
    return Order::create([
        'user_id' => User::factory()->create(['role' => 'user'])->id,
        'sale_type' => 'online', 'status' => $status, 'total_amount' => 2500,
        'full_name' => 'Buyer Name', 'phone_number' => '09171234567',
        'street' => '1 Street', 'barangay' => 'Brgy', 'city' => 'City', 'province' => 'Province', 'postal_code' => '1100',
    ]);
}

it('only issues a mobile token to an active super admin', function () {
    $super = superAdminUser();
    $this->postJson('/api/login', ['email' => $super->email, 'password' => 'password'])
        ->assertOk()->assertJsonPath('user.role', 'super_admin')->assertJsonStructure(['token', 'user' => ['name', 'email', 'initials']]);

    foreach (['admin', 'user'] as $role) {
        $account = User::factory()->create(['role' => $role, 'is_active' => true]);
        $this->postJson('/api/login', ['email' => $account->email, 'password' => 'password'])
            ->assertForbidden()->assertJsonPath('message', 'This app is reserved for Super Admin accounts.');
    }

    // A suspended account answers exactly like a wrong password.
    $this->postJson('/api/login', ['email' => superAdminUser(false)->email, 'password' => 'password'])->assertUnauthorized();
    $this->postJson('/api/login', ['email' => 'nobody@example.test', 'password' => 'wrong'])->assertUnauthorized();
});

it('rejects every protected endpoint without a super admin token', function () {
    $endpoints = ['dashboard', 'orders', 'approvals', 'users', 'users/counts', 'activity-logs', 'notifications', 'inventory', 'me'];
    foreach ($endpoints as $endpoint) {
        $this->getJson('/api/'.$endpoint)->assertUnauthorized();
    }

    Sanctum::actingAs(User::factory()->create(['role' => 'admin']));
    foreach ($endpoints as $endpoint) {
        $this->getJson('/api/'.$endpoint)->assertForbidden();
    }
});

it('returns the full system overview on the mobile dashboard', function () {
    Sanctum::actingAs(superAdminUser());
    superAdminOrder('paid');
    superAdminOrder('completed');
    User::factory()->create(['role' => 'user', 'is_active' => false]);
    User::factory()->create(['role' => 'admin']);
    AdminInvitation::create(['email' => 'pending@example.test', 'inviter_id' => superAdminUser()->id,
        'token_hash' => hash('sha256', str_repeat('a', 64)), 'expires_at' => now()->addDay()]);

    $this->getJson('/api/dashboard')->assertOk()->assertJsonStructure([
        'generated_at', 'summary', 'sales_trend', 'recent_orders', 'recent_activity', 'alerts',
    ])->assertJsonPath('summary.orders_total', 2)
        ->assertJsonPath('summary.orders_paid', 1)
        ->assertJsonPath('summary.orders_completed', 1)
        ->assertJsonPath('summary.users_total', 3)
        ->assertJsonPath('summary.users_suspended', 1)
        ->assertJsonPath('summary.admins_total', 1)
        ->assertJsonPath('summary.invitations_pending', 1)
        ->assertJsonPath('summary.approvals_pending', 0);
});

it('lists the approval queue and applies reviews through the shared service', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $super = superAdminUser();
    ApprovalRequest::create(['requester_id' => $admin->id, 'entity_type' => 'category', 'payload' => ['category_name' => 'Running']]);
    Sanctum::actingAs($super);

    $list = $this->getJson('/api/approvals')->assertOk()->assertJsonPath('total', 1)
        ->assertJsonPath('data.0.summary', 'Category: Running');
    $id = $list->json('data.0.id');

    $this->getJson('/api/approvals/'.$id)->assertOk()
        ->assertJsonStructure(['fields' => [['key', 'label', 'value']], 'requester' => ['name', 'email'], 'images'])
        ->assertJsonPath('fields.0.value', 'Running');

    $this->postJson('/api/approvals/review', ['ids' => [$id], 'decision' => 'approved'])
        ->assertOk()->assertJsonPath('reviewed', [$id])->assertJsonPath('failed', []);
    $this->assertDatabaseHas('categories', ['category_name' => 'Running']);
    expect(ApprovalRequest::find($id)->status)->toBe('approved');
});

it('requires a reason to reject and reports partial failures', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $super = superAdminUser();
    $first = ApprovalRequest::create(['requester_id' => $admin->id, 'entity_type' => 'brand', 'payload' => ['brand_name' => 'Brand']]);
    $second = ApprovalRequest::create(['requester_id' => $admin->id, 'entity_type' => 'brand', 'payload' => ['brand_name' => 'Other']]);
    app(ApprovalService::class)->review($first->id, $super, 'approved');
    Sanctum::actingAs($super);

    $this->postJson('/api/approvals/review', ['ids' => [$second->id], 'decision' => 'rejected'])->assertUnprocessable();
    $this->postJson('/api/approvals/review', ['ids' => [$first->id, $second->id], 'decision' => 'rejected', 'reason' => 'Duplicate'])
        ->assertOk()->assertJsonPath('failed.0.id', $first->id)->assertJsonPath('reviewed', [$second->id]);
    expect($second->fresh()->rejection_reason)->toBe('Duplicate');
});

it('lists and searches customers and admins with account counts', function () {
    $admin = User::factory()->create(['role' => 'admin', 'first_name' => 'Ada', 'last_name' => 'Admin']);
    User::factory()->create(['role' => 'user', 'is_active' => false, 'first_name' => 'Cara', 'last_name' => 'Customer']);
    Sanctum::actingAs(superAdminUser());

    $this->getJson('/api/users')->assertOk()->assertJsonPath('total', 1)->assertJsonPath('data.0.name', 'Cara Customer');
    $this->getJson('/api/users?role=admin')->assertOk()->assertJsonPath('total', 1)->assertJsonPath('data.0.name', 'Ada Admin');
    $this->getJson('/api/users?role=user&status=suspended')->assertOk()->assertJsonPath('total', 1);
    $this->getJson('/api/users?role=admin&search=Ada')->assertOk()->assertJsonPath('total', 1);
    $this->getJson('/api/users/counts')->assertOk()
        ->assertJsonPath('users.total', 1)->assertJsonPath('users.suspended', 1)
        ->assertJsonPath('admins.total', 1)->assertJsonPath('admins.active', 1);
    $this->getJson('/api/admins/'.$admin->id)->assertOk()->assertJsonPath('role_label', 'Admin')
        ->assertJsonPath('can_suspend', true)->assertJsonStructure(['recent_activity']);
});

it('suspends and restores accounts while protecting super admins and self', function () {
    $super = superAdminUser();
    $customer = User::factory()->create(['role' => 'user']);
    $otherSuper = superAdminUser();
    $token = $customer->createToken('x');
    Sanctum::actingAs($super);

    $this->patchJson('/api/users/'.$customer->id.'/status', ['is_active' => false])
        ->assertOk()->assertJsonPath('user.is_active', false);
    $this->assertDatabaseCount('personal_access_tokens', 0); // suspended devices are revoked

    $this->patchJson('/api/users/'.$customer->id.'/status', ['is_active' => true])->assertOk();
    $this->patchJson('/api/users/'.$otherSuper->id.'/status', ['is_active' => false])->assertForbidden();
    $this->patchJson('/api/users/'.$super->id.'/status', ['is_active' => false])->assertForbidden();
    $this->assertTrue((bool) $otherSuper->fresh()->is_active);
});

it('invites an admin through the existing invitation workflow', function () {
    Notification::fake();
    Sanctum::actingAs(superAdminUser());

    $this->postJson('/api/admin-invitations', ['email' => 'invited@example.test'])
        ->assertStatus(201)->assertJsonPath('invitation.email', 'invited@example.test');
    $this->assertDatabaseHas('admin_invitations', ['email' => 'invited@example.test']);

    // Re-inviting rotates the existing token instead of creating a second row.
    $this->postJson('/api/admin-invitations', ['email' => 'invited@example.test'])->assertStatus(201);
    $this->assertDatabaseCount('admin_invitations', 1);

    $this->getJson('/api/admin-invitations')->assertOk()->assertJsonPath('total', 1)
        ->assertJsonPath('data.0.email', 'invited@example.test');

    $existing = User::factory()->create(['email' => 'taken@example.test']);
    $this->postJson('/api/admin-invitations', ['email' => $existing->email])->assertUnprocessable();
});

it('answers the dashboard within a small query budget', function () {
    Sanctum::actingAs(superAdminUser());
    superAdminOrder('paid');
    superAdminOrder('completed');
    Cache::flush();

    DB::enableQueryLog();
    $this->getJson('/api/dashboard?fresh=1')->assertOk()
        ->assertJsonStructure(['badges' => ['unread_notifications', 'pending_approvals'], 'summary', 'sales_trend'])
        ->assertJsonPath('badges.pending_approvals', 0);
    $queries = count(DB::getQueryLog());
    DB::disableQueryLog();

    // This screen used to run 48 sequential round trips against the remote
    // database. The budget is what keeps that from creeping back in.
    expect($queries)->toBeLessThan(16);
});

it('serves a cached dashboard unless fresh data is requested', function () {
    Sanctum::actingAs(superAdminUser());
    superAdminOrder('paid');
    Cache::flush();

    expect($this->getJson('/api/dashboard')->json('summary.orders_total'))->toBe(1);

    superAdminOrder('paid');
    // Cached: a new order is not visible until the cache expires...
    expect($this->getJson('/api/dashboard')->json('summary.orders_total'))->toBe(1);
    // ...but pull-to-refresh bypasses and re-warms it.
    expect($this->getJson('/api/dashboard?fresh=1')->json('summary.orders_total'))->toBe(2);
    expect($this->getJson('/api/dashboard')->json('summary.orders_total'))->toBe(2);
});

it('loads the audit trail without one query per row', function () {
    Sanctum::actingAs(superAdminUser());
    $order = superAdminOrder('paid');
    foreach (range(1, 12) as $index) {
        ActivityLog::create([
            'user_id' => null,
            'action' => 'order.updated',
            'subject_type' => Order::class,
            'subject_id' => $order->order_id,
        ]);
    }

    DB::enableQueryLog();
    $response = $this->getJson('/api/activity-logs')->assertOk()
        ->assertJsonPath('data.0.subject', 'Buyer Name');
    $queries = count(DB::getQueryLog());
    DB::disableQueryLog();

    expect($response->json('total'))->toBeGreaterThanOrEqual(13);
    expect($queries)->toBeLessThan(12);
});

it('answers the account badge counts in two round trips', function () {
    Sanctum::actingAs(superAdminUser());
    User::factory()->count(3)->create(['role' => 'user']);

    DB::enableQueryLog();
    $this->getJson('/api/users/counts')->assertOk()->assertJsonPath('users.total', 3);
    $queries = count(DB::getQueryLog());
    DB::disableQueryLog();

    expect($queries)->toBeLessThan(8);
});
it('filters the audit trail and exposes filter options', function () {
    $super = superAdminUser();
    Sanctum::actingAs($super);
    $order = superAdminOrder();
    $order->update(['status' => 'shipped']);

    $list = $this->getJson('/api/activity-logs?category=order')->assertOk()->assertJsonPath('total', 2)
        ->assertJsonPath('data.0.subject_type', Order::class);
    $this->getJson('/api/activity-logs?category=order&event=created')->assertOk()->assertJsonPath('total', 1);

    $this->getJson('/api/activity-logs/'.$list->json('data.0.id'))->assertOk()
        ->assertJsonPath('action', 'order.updated')
        ->assertJsonPath('subject_id', $order->order_id)
        ->assertJsonStructure(['changes' => ['status' => ['old', 'new']]]);
    $this->getJson('/api/activity-logs/999999')->assertNotFound();
    $this->getJson('/api/activity-logs?search=Buyer')->assertOk();
    $this->getJson('/api/activity-logs?user_id='.$super->id)->assertOk();
    $this->getJson('/api/activity-logs/filters')->assertOk()->assertJsonStructure(['categories', 'events', 'users']);
});

it('exposes a read-only inventory overview with low and out of stock filters', function () {
    Sanctum::actingAs(superAdminUser());
    $shoeType = \App\Models\ShoeType::create(['shoe_type_name' => 'Sneaker']);
    $product = Product::create(['product_name' => 'Runner', 'category_id' => Category::create(['category_name' => 'Cat'])->category_id,
        'brand_id' => Brand::create(['brand_name' => 'Brand'])->brand_id, 'shoe_type_id' => $shoeType->shoe_type_id]);
    $variant = \App\Models\ProductVariant::create(['product_id' => $product->product_id, 'size' => '9', 'color' => 'Black']);
    \App\Models\Stock::create(['product_variant_id' => $variant->product_variant_id, 'price' => 100,
        'received_quantity' => 3, 'remaining_quantity' => 3, 'deliver_date' => now()->toDateString()]);

    $this->getJson('/api/inventory')->assertOk()->assertJsonPath('total', 1)
        ->assertJsonPath('data.0.remaining', 3)->assertJsonPath('data.0.state', 'low')->assertJsonPath('data.0.value', 300);
    $this->getJson('/api/inventory?filter=low')->assertOk()->assertJsonPath('total', 1);
    $this->getJson('/api/inventory?filter=out')->assertOk()->assertJsonPath('total', 0);
    $this->getJson('/api/inventory?search=Runner')->assertOk()->assertJsonPath('total', 1);
});

it('alerts the super admin when stock crosses into low and out of stock', function () {
    $super = superAdminUser();
    $shoeType = \App\Models\ShoeType::create(['shoe_type_name' => 'Sneaker']);
    $product = Product::create(['product_name' => 'Runner', 'category_id' => Category::create(['category_name' => 'Cat'])->category_id,
        'brand_id' => Brand::create(['brand_name' => 'Brand'])->brand_id, 'shoe_type_id' => $shoeType->shoe_type_id]);
    $variant = \App\Models\ProductVariant::create(['product_id' => $product->product_id, 'size' => '9', 'color' => 'Black']);
    $stock = \App\Models\Stock::create(['product_variant_id' => $variant->product_variant_id, 'price' => 100,
        'received_quantity' => 20, 'remaining_quantity' => 20, 'deliver_date' => now()->toDateString()]);
    expect($super->fresh()->unreadNotifications()->count())->toBe(0);

    $stock->update(['remaining_quantity' => 4]);
    $this->assertDatabaseHas('notifications', ['notifiable_id' => $super->id]);
    expect($super->fresh()->unreadNotifications()->count())->toBe(1);
    expect($super->fresh()->notifications()->first()->data['type'])->toBe('inventory_low_stock');

    // Still low: the alert is debounced rather than repeated on every sale.
    $stock->update(['remaining_quantity' => 3]);
    expect($super->fresh()->unreadNotifications()->count())->toBe(1);

    $stock->update(['remaining_quantity' => 0]);
    expect($super->fresh()->unreadNotifications()->count())->toBe(2);
});