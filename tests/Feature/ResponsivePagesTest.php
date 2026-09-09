<?php

use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

beforeEach(fn () => Http::fake());

it('serves public pages with stable local styling', function (string $path) {
    $response = $this->get($path)->assertOk();
    $response->assertSee('name="viewport"', false)
        ->assertSee('css/responsive.css', false)
        ->assertSee('js/responsive.js', false)
        ->assertDontSee('cdn.tailwindcss.com', false)
        ->assertDontSee('setViewportHeight', false);
})->with(['/', '/login', '/register', '/password/reset']);

it('renders customer pages with the shared responsive assets', function (string $path) {
    $user = User::factory()->create(['role' => 'user', 'email_verified_at' => now()]);
    $this->actingAs($user)->get($path)->assertOk()
        ->assertSee('css/responsive.css', false)
        ->assertSee('js/responsive.js', false)
        ->assertDontSee('cdn.tailwindcss.com', false);
})->with(['/home', '/men', '/women', '/kids', '/new', '/search?q=shoe', '/profile', '/addresses', '/addresses/create', '/orders', '/cart']);

it('renders admin pages without the runtime css compiler', function (string $path) {
    if (DB::getDriverName() === 'sqlite' && in_array($path, ['/admin/reports', '/admin/logs'])) {
        $this->markTestSkipped('This controller uses database-specific SQL unsupported by the SQLite test database.');
    }
    $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
    $this->actingAs($admin)->get($path)->assertOk()
        ->assertSee('css/responsive.css', false)
        ->assertSee('js/responsive.js', false)
        ->assertDontSee('cdn.tailwindcss.com', false);
})->with(['/admin/dashboard', '/admin/categories', '/admin/brands', '/admin/shoe-types', '/admin/products', '/admin/users/create-admin', '/admin/inventory', '/admin/orders', '/admin/users', '/admin/reports', '/admin/logs', '/admin/settings', '/admin/pos']);

it('renders an existing address and its edit form with responsive styling', function () {
    $user = User::factory()->create(['role' => 'user', 'email_verified_at' => now()]);
    $address = UserAddress::create([
        'user_id' => $user->id, 'full_name' => 'Mobile Test Customer',
        'phone_number' => '09171234567', 'street' => '123 Example Street',
        'barangay' => 'San Antonio', 'city' => 'Quezon City',
        'province' => 'Metro Manila', 'region' => 'NCR', 'postal_code' => '1100',
    ]);
    $this->actingAs($user)->get('/addresses')->assertOk()->assertSee('confirm-address-deletion');
    $this->get('/addresses/'.$address->address_id.'/edit')->assertOk()
        ->assertSee('css/responsive.css', false)->assertSee('p-4 sm:p-8', false);
});

