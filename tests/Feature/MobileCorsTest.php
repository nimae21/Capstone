<?php

/*
|--------------------------------------------------------------------------
| Mobile CORS
|--------------------------------------------------------------------------
| The Capacitor WebView serves the app from a local origin, so every API call
| is cross-origin. If these headers are missing the phone reports a generic
| "unable to connect" error even with working internet, so this is a
| regression guard, not a nicety.
*/

function preflight(string $path, string $origin, string $method)
{
    return test()->call('OPTIONS', $path, [], [], [], [
        'HTTP_ORIGIN' => $origin,
        'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => $method,
        'HTTP_ACCESS_CONTROL_REQUEST_HEADERS' => 'content-type,accept,authorization',
    ]);
}

it('allows the Capacitor app origins to call the API', function () {
    foreach (['https://localhost', 'http://localhost', 'capacitor://localhost'] as $origin) {
        preflight('/api/login', $origin, 'POST')
            ->assertNoContent()
            ->assertHeader('Access-Control-Allow-Origin', $origin)
            ->assertHeader('Access-Control-Allow-Methods');
    }
});

it('allows the PATCH method used by account suspension', function () {
    $response = preflight('/api/users/1/status', 'https://localhost', 'PATCH')->assertNoContent();
    expect($response->headers->get('Access-Control-Allow-Methods'))->toContain('PATCH');
});

it('still allows the website origin when CORS_ALLOWED_ORIGINS is configured', function () {
    config(['cors.allowed_origins' => array_merge(
        (array) config('cors.allowed_origins'),
        ['https://achilleswearyourweakness.shop'],
    )]);

    preflight('/api/login', 'https://achilleswearyourweakness.shop', 'POST')
        ->assertNoContent()
        ->assertHeader('Access-Control-Allow-Origin', 'https://achilleswearyourweakness.shop');
});

it('does not open CORS up to arbitrary websites', function () {
    preflight('/api/login', 'https://evil.test', 'POST')->assertNoContent();
    expect(preflight('/api/login', 'https://evil.test', 'POST')->headers->get('Access-Control-Allow-Origin'))->toBeNull();
});