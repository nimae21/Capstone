<?php

namespace Tests\Governance;

use App\Models\AdminInvitation;
use App\Models\ApprovalRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\PendingRegistration;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShoeType;
use App\Models\Stock;
use App\Models\User;
use App\Notifications\AccountLinkNotification;
use App\Services\ApprovalService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GovernanceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Fail closed before touching any database. No refresh/reset command is used.
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->assertEmpty(config('database.connections.sqlite.url'));
        foreach (glob(database_path('migrations/*.php')) as $file) {
            (require $file)->up();
        }
        Notification::fake();
        Http::fake();
        Storage::fake('supabase');
        $this->withoutVite();
    }

    private function account(string $role = 'user'): User
    {
        return User::factory()->create(['role' => $role, 'is_active' => true]);
    }

    private function registration(string $email = 'new@example.test'): array
    {
        return ['first_name' => 'First', 'middle_name' => 'Middle', 'last_name' => 'Last', 'suffix' => 'Jr.',
            'email' => $email, 'password' => 'Password123!', 'password_confirmation' => 'Password123!', 'terms' => 1];
    }

    private function proposal(User $admin, string $type = 'category', array $payload = ['category_name' => 'Proposed']): ApprovalRequest
    {
        return ApprovalRequest::create(['requester_id' => $admin->id, 'entity_type' => $type, 'payload' => $payload]);
    }

    public function test_registration_is_pending_until_single_use_verification(): void
    {
        $this->post('/register', $this->registration(' NEW@example.test '))->assertRedirect(route('login'));
        $this->assertDatabaseCount('users', 0);
        $pending = PendingRegistration::firstOrFail();
        $this->assertSame('new@example.test', $pending->email);
        $this->assertTrue(Hash::check('Password123!', $pending->payload['password']));
        $this->assertStringNotContainsString('Password123!', DB::table('pending_registrations')->value('payload'));
        $url = null;
        Notification::assertSentOnDemand(AccountLinkNotification::class, function ($n) use (&$url) {
            $url = $n->url;

            return true;
        });
        $this->get($url)->assertRedirect(route('login'));
        $this->assertDatabaseCount('pending_registrations', 0);
        $user = User::firstOrFail();
        $this->assertSame('user', $user->role);
        $this->assertNotNull($user->email_verified_at);
        $this->assertSame('Middle', $user->middle_name);
        $this->assertSame('Jr.', $user->suffix);
        $this->assertGuest();
        $this->get($url)->assertStatus(410);
    }

    public function test_registration_reuses_email_rotates_token_and_limits_attempts(): void
    {
        $this->post('/register', $this->registration())->assertRedirect();
        $old = PendingRegistration::first()->token_hash;
        $this->post('/register', $this->registration())->assertRedirect();
        $this->assertDatabaseCount('pending_registrations', 1);
        $this->assertNotSame($old, PendingRegistration::first()->token_hash);
        $this->post('/register', $this->registration())->assertRedirect();
        $this->post('/register', $this->registration())->assertSessionHasErrors('email');
        $this->assertDatabaseCount('users', 0);
    }

    public function test_expired_pending_records_can_be_pruned_without_touching_users(): void
    {
        $this->account();
        PendingRegistration::create(['email' => 'expired@example.test', 'payload' => $this->registration(), 'token_hash' => hash('sha256', str_repeat('a', 64)), 'expires_at' => now()->subMinute()]);
        $this->get('/registration/verify/'.str_repeat('a', 64))->assertStatus(410);
        $this->artisan('registrations:prune')->assertSuccessful();
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('pending_registrations', 0);
    }

    public function test_existing_email_cannot_register(): void
    {
        $user = $this->account();
        $this->post('/register', $this->registration($user->email))->assertSessionHasErrors('email');
        $this->assertDatabaseCount('pending_registrations', 0);
    }

    public function test_invitation_fixes_email_and_is_single_use(): void
    {
        $super = $this->account('super_admin');
        $this->actingAs($super)->post(route('admin.users.store-admin'), ['email' => 'invited@example.test'])->assertRedirect();
        $url = null;
        Notification::assertSentOnDemand(AccountLinkNotification::class, function ($n) use (&$url) {
            $url = $n->url;

            return $n->invitation;
        });
        auth()->logout();
        $this->get($url)->assertOk()->assertSee('invited@example.test');
        $this->post($url, $this->registration('tampered@example.test'))->assertRedirect(route('login'));
        $this->assertDatabaseHas('users', ['email' => 'invited@example.test', 'role' => 'admin']);
        $this->assertDatabaseMissing('users', ['email' => 'tampered@example.test']);
        $this->assertNotNull(AdminInvitation::first()->accepted_at);
        $this->post($url, $this->registration())->assertStatus(410);
    }

    public function test_admin_cannot_govern_and_super_cannot_operate(): void
    {
        $admin = $this->account('admin');
        $super = $this->account('super_admin');
        $user = $this->account();
        $this->actingAs($admin)->get(route('admin.users.index'))->assertForbidden();
        $this->post(route('admin.users.store-admin'), ['email' => 'bad@example.test'])->assertForbidden();
        $this->patch(route('admin.users.toggle-status', $user), ['is_active' => 0])->assertForbidden();
        $this->post(route('governance.review'), ['ids' => [1], 'decision' => 'approved'])->assertForbidden();
        $this->post('/admin/users/create-admin', $this->registration())->assertNotFound();
        $this->actingAs($super)->post('/admin/categories', ['category_name' => 'Forbidden'])->assertForbidden();
        $this->post('/admin/pos/sale', [])->assertForbidden();
        $this->patch(route('admin.users.toggle-status', $super), ['is_active' => 0])->assertForbidden();
        $this->get('/admin/categories')->assertOk();
        $this->get(route('governance.approvals'))->assertOk();
        $this->get(route('admin.users.index'))->assertOk();
    }

    public function test_suspension_blocks_sessions_api_and_login(): void
    {
        $super = $this->account('super_admin');
        $admin = $this->account('admin');
        $token = $admin->createToken('test')->plainTextToken;
        $this->actingAs($super)->patch(route('admin.users.toggle-status', $admin), ['is_active' => 0])->assertRedirect();
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->actingAs($admin->fresh())->get('/admin/categories')->assertRedirect(route('login'));
        $this->assertGuest();
        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->postJson('/api/login', ['email' => $admin->email, 'password' => 'password'])->assertUnauthorized();
        $this->actingAs($super)->patch(route('admin.users.toggle-status', $admin), ['is_active' => 1])->assertRedirect();
        $this->assertTrue((bool) $admin->fresh()->is_active);
    }

    public function test_login_rate_limits_normalized_email(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => 'no@example.test', 'password' => 'incorrect'])->assertSessionHasErrors('email');
        }
        $response = $this->postJson('/login', ['email' => 'NO@example.test', 'password' => 'incorrect']);
        $response->assertRedirect()->assertSessionHasErrors('email');
        $this->assertStringContainsString('Too many login attempts', session('errors')->first('email'));
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/login', ['email' => 'api@example.test', 'password' => 'wrong'])->assertUnauthorized();
        }
        $this->postJson('/api/login', ['email' => 'API@example.test', 'password' => 'wrong'])->assertStatus(429);
    }

    public function test_catalog_addition_requires_approval_and_replay_is_safe(): void
    {
        $admin = $this->account('admin');
        $super = $this->account('super_admin');
        $this->actingAs($admin)->postJson('/admin/categories', ['category_name' => 'New Category'])->assertStatus(202)->assertJsonPath('pending', true);
        $this->assertDatabaseCount('categories', 0);
        $item = ApprovalRequest::firstOrFail();
        $this->actingAs($super)->post(route('governance.review'), ['ids' => [$item->id], 'decision' => 'approved'])->assertRedirect();
        $this->assertDatabaseHas('categories', ['category_name' => 'New Category']);
        $this->assertSame('approved', $item->fresh()->status);
        $this->post(route('governance.review'), ['ids' => [$item->id], 'decision' => 'approved'])->assertSessionHas('review_errors');
        $this->assertDatabaseCount('categories', 1);
    }

    public function test_bulk_review_is_bounded_and_reports_conflicts_and_self_review(): void
    {
        $admin = $this->account('admin');
        $super = $this->account('super_admin');
        $a = $this->proposal($admin);
        $b = $this->proposal($admin);
        $own = $this->proposal($super, 'brand', ['brand_name' => 'Own']);
        $this->actingAs($super)->post(route('governance.review'), ['ids' => [$a->id, $b->id, $own->id], 'decision' => 'approved'])->assertRedirect()->assertSessionHas('review_errors');
        $this->assertSame('approved', $a->fresh()->status);
        $this->assertSame('pending', $b->fresh()->status);
        $this->assertSame('pending', $own->fresh()->status);
        $this->post(route('governance.review'), ['ids' => [$b->id], 'decision' => 'rejected'])->assertSessionHasErrors('reason');
        $this->post(route('governance.review'), ['ids' => [$b->id], 'decision' => 'rejected', 'reason' => 'Duplicate'])->assertRedirect();
        $this->assertSame('rejected', $b->fresh()->status);
        $this->assertSame('Duplicate', $b->fresh()->rejection_reason);
        $this->post(route('governance.review'), ['ids' => range(1, 101), 'decision' => 'approved'])->assertSessionHasErrors('ids');
    }

    public function test_all_creation_types_and_images_wait_for_approval(): void
    {
        $admin = $this->account('admin');
        $super = $this->account('super_admin');
        $service = app(ApprovalService::class);
        $this->actingAs($admin)->post('/admin/brands', ['brand_name' => 'Brand'])->assertRedirect();
        $this->post('/admin/shoe-types', ['shoe_type_name' => 'Type', 'description' => 'Test'])->assertRedirect();
        $this->post('/admin/categories', ['category_name' => 'Category'])->assertRedirect();
        foreach (ApprovalRequest::pluck('id') as $id) {
            $service->review($id, $super, 'approved');
        }
        $this->post('/admin/products', ['product_name' => 'Product', 'category_id' => Category::first()->getKey(), 'brand_id' => Brand::first()->getKey(), 'shoe_type_id' => ShoeType::first()->getKey(), 'images' => [UploadedFile::fake()->createWithContent('shoe.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aOZkAAAAASUVORK5CYII='))]])->assertRedirect();
        $this->assertDatabaseCount('products', 0);
        $this->assertDatabaseCount('product_images', 0);
        $service->review(ApprovalRequest::max('id'), $super, 'approved');
        $product = Product::firstOrFail();
        $this->assertDatabaseCount('product_images', 1);
        $this->post('/admin/products/'.$product->getKey().'/variants', ['size' => '8', 'color' => 'red'])->assertRedirect();
        $this->assertDatabaseCount('product_variants', 0);
        $service->review(ApprovalRequest::max('id'), $super, 'approved');
        $variant = ProductVariant::firstOrFail();
        $this->post('/admin/variants/'.$variant->getKey().'/stocks', ['received_quantity' => 12, 'price' => 100, 'deliver_date' => now()->toDateString()])->assertRedirect();
        $this->assertDatabaseCount('stocks', 0);
        $service->review(ApprovalRequest::max('id'), $super, 'approved');
        $this->assertEquals(12, Stock::first()->remaining_quantity);
        $this->assertDatabaseHas('stock_movements', ['quantity' => 12, 'type' => 'in']);
        $this->post('/admin/products/'.$product->getKey().'/images', ['images' => [UploadedFile::fake()->createWithContent('second.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aOZkAAAAASUVORK5CYII='))]])->assertRedirect();
        $this->assertDatabaseCount('product_images', 1);
        $service->review(ApprovalRequest::max('id'), $super, 'approved');
        $this->assertDatabaseCount('product_images', 2);
    }

    public function test_guest_catalog_and_protected_actions(): void
    {
        $this->get('/')->assertOk()->assertDontSee('data-auth', false)->assertSee('guest-auth-prompt', false);
        foreach (['/', '/home', '/men', '/women', '/kids', '/new', '/products', '/search?q=shoe'] as $url) {
            $this->get($url)->assertOk();
        }
        foreach (['/cart', '/checkout', '/profile', '/addresses', '/orders'] as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
        $this->post('/cart/add', [])->assertRedirect(route('login'));
    }

    public function test_large_queue_paginates_and_reviews_only_selected_hundred(): void
    {
        $admin = $this->account('admin');
        $super = $this->account('super_admin');
        foreach (range(1, 10) as $batch) {
            $rows = [];
            foreach (range(1, 100) as $index) {
                $rows[] = ['requester_id' => $admin->id, 'entity_type' => 'category',
                    'payload' => json_encode(['category_name' => 'Category '.($batch * 100 + $index)]), 'created_at' => now(), 'updated_at' => now()];
            }
            DB::table('approval_requests')->insert($rows);
        }
        $page = $this->actingAs($super)->get(route('governance.approvals'))->assertOk();
        $this->assertSame(1000, $page->viewData('requests')->total());
        $this->assertCount(25, $page->viewData('requests')->items());
        $this->post(route('governance.review'), ['ids' => range(1, 100), 'decision' => 'approved'])->assertRedirect();
        $this->assertSame(100, ApprovalRequest::where('status', 'approved')->count());
        $this->assertSame(900, ApprovalRequest::where('status', 'pending')->count());
        $this->assertDatabaseCount('categories', 100);
    }

    public function test_expired_invitation_and_suspended_requester_are_rejected(): void
    {
        $super = $this->account('super_admin');
        $admin = $this->account('admin');
        AdminInvitation::create(['email' => 'expired@example.test', 'inviter_id' => $super->id,
            'token_hash' => hash('sha256', str_repeat('a', 64)), 'expires_at' => now()->subMinute()]);
        $this->get('/admin-invitations/'.str_repeat('a', 64))->assertStatus(410);
        $this->post('/admin-invitations/'.str_repeat('a', 64), $this->registration())->assertStatus(410);
        $item = $this->proposal($admin);
        $admin->update(['is_active' => false]);
        $this->actingAs($super)->post(route('governance.review'), ['ids' => [$item->id], 'decision' => 'approved'])->assertSessionHas('review_errors');
        $this->assertSame('pending', $item->fresh()->status);
        $this->assertDatabaseCount('categories', 0);
    }

    public function test_legacy_mixed_case_email_is_not_duplicated_and_can_login(): void
    {
        $user = $this->account();
        $user->update(['email' => 'Legacy@Example.test']);
        $this->post('/register', $this->registration('legacy@example.test'))->assertSessionHasErrors('email');
        $this->assertDatabaseCount('pending_registrations', 0);
        $this->post('/login', ['email' => 'legacy@example.test', 'password' => 'password'])->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }
}
