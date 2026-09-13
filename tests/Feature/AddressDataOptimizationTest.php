<?php

use App\Models\User;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
    $this->customer = User::factory()->create([
        'role' => 'user',
        'email_verified_at' => now(),
    ]);
});

it('requires an authenticated verified customer for address data', function () {
    $this->get('/address-data/barangays/137404')->assertRedirect('/login');
});

it('returns only the indexed barangay card fields with immutable versioned caching', function () {
    $response = $this->actingAs($this->customer)
        ->getJson('/address-data/barangays/137404?v='.config('address-data.version'))
        ->assertOk()
        ->assertJsonPath('city_code', '137404')
        ->assertJsonCount(142, 'barangays')
        ->assertJsonPath('barangays.0.name', 'Alicia')
        ->assertJsonMissingPath('barangays.0.brgy_code')
        ->assertJsonMissingPath('barangays.0.city_code');

    expect($response->headers->get('Cache-Control'))->toContain('public')
        ->toContain('immutable')
        ->and($response->headers->get('ETag'))->not->toBeNull();

    $this->withHeader('If-None-Match', $response->headers->get('ETag'))
        ->get('/address-data/barangays/137404?v='.config('address-data.version'))
        ->assertNotModified();
});

it('rejects invalid, unknown, and path-like city identifiers', function (string $path) {
    $this->actingAs($this->customer)->get($path)->assertNotFound();
})->with([
    '/address-data/barangays/not-a-code',
    '/address-data/barangays/999999',
    '/address-data/barangays/%2E%2E%2F.env',
]);

it('does not reference the full barangay dataset on initial address pages', function (string $path) {
    $response = $this->actingAs($this->customer)->get($path)->assertOk();
    $response->assertDontSee('barangay.json', false)
        ->assertDontSee('unpkg.com/leaflet', false)
        ->assertSee('address-selector-', false)
        ->assertSee('/address-data/barangays/__CITY__?v=', false);
})->with(['/addresses/create']);

it('keeps checkout on the lazy address endpoint and Vite assets', function () {
    $source = file_get_contents(resource_path('views/checkout/index.blade.php'));

    expect($source)->not->toContain('barangay.json')
        ->not->toContain('unpkg.com/leaflet')
        ->toContain('resources/js/address-selector.js')
        ->toContain('resources/js/checkout-address.js');
});

it('hydrates existing address selections through the shared selector', function () {
    $address = $this->customer->addresses()->create([
        'full_name' => 'Existing Customer',
        'phone_number' => '09171234567',
        'street' => '123 Existing Street',
        'region' => 'National Capital Region (NCR)',
        'province' => 'Metro Manila',
        'city' => 'Quezon City',
        'barangay' => 'Alicia',
        'postal_code' => '1105',
        'latitude' => '14.6760',
        'longitude' => '121.0437',
    ]);

    $this->actingAs($this->customer)->get(route('addresses.edit', $address))
        ->assertOk()
        ->assertSee('data-default="Quezon City"', false)
        ->assertSee('data-default="Alicia"', false)
        ->assertSee('value="14.676', false)
        ->assertSee('address-selector-', false);
});
