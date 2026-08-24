<?php

use App\Models\User;
use App\Models\UserAddress;

it('stores address data including the region and coordinates without forcing the default flag', function () {
    $user = User::factory()->create([
        'role' => 'user',
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)->post('/addresses', [
        'full_name' => 'Jane Doe',
        'phone_number' => '09171234567',
        'street' => '123 Example Street',
        'barangay' => 'San Antonio',
        'city' => 'Quezon City',
        'province' => 'Metro Manila',
        'region' => 'NCR',
        'postal_code' => '1100',
        'latitude' => '14.6760',
        'longitude' => '121.0437',
        'is_default' => '0',
    ]);

    $response->assertRedirect('/addresses');

    $address = UserAddress::query()->first();

    expect($address)->not->toBeNull()
        ->and($address->region)->toBe('NCR')
        ->and((float) $address->latitude)->toBeFloat()
        ->and((float) $address->longitude)->toBeFloat()
        ->and($address->is_default)->toBeFalse();
});
