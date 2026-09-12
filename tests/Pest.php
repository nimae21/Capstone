<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/**
 * A structurally valid but cryptographically worthless service-account key.
 *
 * Push tests need something that passes credential validation; this is a
 * fixture, not a Firebase secret, and it can never authenticate with Google.
 * Real credentials only ever live in the host's environment variables. The
 * PEM header is assembled from parts so secret scanners do not mistake the
 * fixture for a leaked key.
 */
function serviceAccountFixture(array $overrides = []): array
{
    $header = '-----BEGIN '.'PRIVATE KEY-----';
    $footer = '-----END '.'PRIVATE KEY-----';

    return array_merge([
        'type' => 'service_account',
        'project_id' => 'achilles-fixture-project',
        'private_key_id' => 'fixture-key-id',
        'client_email' => 'push@achilles-fixture-project.iam.gserviceaccount.com',
        'private_key' => $header."\nFIXTURE-NOT-A-REAL-KEY\n".$footer."\n",
    ], $overrides);
}
