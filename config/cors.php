<?php

$browserOrigins = array_values(array_filter(array_map('trim', explode(',', (string) env('CORS_ALLOWED_ORIGINS', '')))));

/*
|--------------------------------------------------------------------------
| Capacitor mobile app origins
|--------------------------------------------------------------------------
| The Android/iOS app is served by the WebView from a local origin, so every
| call to this API is a cross-origin request and is subject to CORS. Without
| these the phone gets a CORS rejection that looks exactly like "no internet".
| The values are fixed by Capacitor and cannot be spoofed by a website, so
| they are always allowed in addition to CORS_ALLOWED_ORIGINS.
*/
$capacitorOrigins = [
    'https://localhost',
    'http://localhost',
    'capacitor://localhost',
    'ionic://localhost',
];

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust your settings as needed.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    // PATCH is required by PATCH /api/users/{id}/status (suspend/reactivate).
    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins' => array_values(array_unique(array_merge($browserOrigins, $capacitorOrigins))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Accept', 'Authorization', 'Content-Type', 'X-CSRF-TOKEN', 'X-XSRF-TOKEN'],

    'exposed_headers' => [],

    'max_age' => 600,

    // The mobile app authenticates with bearer tokens, never cookies.
    'supports_credentials' => false,

];