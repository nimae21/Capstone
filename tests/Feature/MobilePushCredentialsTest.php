<?php

use App\Exceptions\PushSendException;
use App\Models\MobilePushDelivery;
use App\Models\MobilePushDevice;
use App\Models\User;
use App\Services\FirebasePushSender;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/** Writes a fixture key file at runtime, never in the repository. */
function serviceAccountFile(array $account): string
{
    $directory = storage_path('framework/testing/firebase-credentials');
    File::ensureDirectoryExists($directory);
    $path = $directory.'/service-account-'.Str::random(8).'.json';
    file_put_contents($path, json_encode($account));

    return $path;
}

function credentialDevice(User $user): MobilePushDevice
{
    return MobilePushDevice::create([
        'installation_id' => (string) Str::uuid(),
        'user_id' => $user->id,
        'personal_access_token_id' => $user->createToken('mobile-app')->accessToken->id,
        'token' => 'fixture-fcm-token-'.Str::random(20),
        'token_hash' => hash('sha256', Str::random(40)),
        'enabled' => true,
        'last_seen_at' => now(),
    ]);
}

beforeEach(function () {
    Cache::flush();
    config([
        'mobile_push.enabled' => true,
        'mobile_push.project_id' => null,
        'mobile_push.credentials_json' => null,
        'mobile_push.credentials' => null,
    ]);
});

afterEach(function () {
    File::deleteDirectory(storage_path('framework/testing/firebase-credentials'));
});

it('reads the whole service-account key from the environment variable', function () {
    config(['mobile_push.credentials_json' => json_encode(serviceAccountFixture())]);

    $sender = new FirebasePushSender;

    // No file exists anywhere: the variable alone is enough to configure push.
    expect($sender->configured())->toBeTrue()
        ->and($sender->configurationProblem())->toBe('');
});

it('reports configured on the phone status endpoint from the variable alone', function () {
    // FIREBASE_PROJECT_ID is left empty on purpose: the key carries it.
    config(['mobile_push.credentials_json' => json_encode(serviceAccountFixture())]);

    $user = User::factory()->create(['role' => 'super_admin', 'is_active' => true]);
    $token = $user->createToken('mobile-app');

    $this->withToken($token->plainTextToken)
        ->getJson('/api/push/status')
        ->assertOk()
        ->assertJsonPath('configured', true);
});

it('accepts a key pasted with real line breaks in the private key', function () {
    // Railway and other dashboards happily store a value like this: the key is
    // correct, but its line breaks were pasted as real newlines.
    $header = '-----BEGIN '.'PRIVATE KEY-----';
    $footer = '-----END '.'PRIVATE KEY-----';
    $literal = '{"type":"service_account","project_id":"achilles-fixture-project",'
        .'"client_email":"push@achilles-fixture-project.iam.gserviceaccount.com",'
        .'"private_key":"'.$header."\n".'FIXTURE-NOT-A-REAL-KEY'."\n".$footer."\n".'"}';

    config(['mobile_push.credentials_json' => $literal]);

    expect((new FirebasePushSender)->configured())->toBeTrue();
});

it('falls back to the credential file path for local development', function () {
    $path = serviceAccountFile(serviceAccountFixture(['project_id' => 'file-project']));
    config(['mobile_push.credentials' => $path]);

    $sender = new FirebasePushSender;

    expect($sender->configured())->toBeTrue()
        ->and($sender->configurationProblem())->toBe('');
});

it('prefers the environment variable over the file path', function () {
    $path = serviceAccountFile(serviceAccountFixture(['project_id' => 'file-project']));
    config([
        'mobile_push.credentials_json' => json_encode(serviceAccountFixture(['project_id' => 'json-project'])),
        'mobile_push.credentials' => $path,
    ]);

    Http::fake(['fcm.googleapis.com/*' => Http::response(['name' => 'projects/json-project/messages/1'])]);
    $sender = Mockery::mock(FirebasePushSender::class)->makePartial()->shouldAllowMockingProtectedMethods();
    $sender->shouldReceive('accessToken')->andReturn('fake-oauth-token');

    $device = credentialDevice(User::factory()->create(['role' => 'super_admin', 'is_active' => true]));
    $delivery = MobilePushDelivery::create([
        'device_id' => $device->id,
        'personal_access_token_id' => $device->personal_access_token_id,
        'event_key' => 'test:credentials',
        'kind' => 'test',
        'status' => 'pending',
        'available_at' => now(),
    ]);

    expect($sender->send($device, $delivery))->toBe('sent');
    Http::assertSent(fn ($request) => str_contains($request->url(), '/projects/json-project/messages:send'));
});

it('authenticates from the parsed key instead of a file on disk', function () {
    config(['mobile_push.credentials_json' => json_encode(serviceAccountFixture())]);

    $captured = [];
    $capture = function (array $account) use (&$captured) {
        $captured = $account;
    };

    $sender = new class($capture) extends FirebasePushSender
    {
        public function __construct(private readonly Closure $capture) {}

        protected function serviceAccountCredentials(array $account): ServiceAccountCredentials
        {
            ($this->capture)($account);

            throw new RuntimeException('Signing is never reached in this test.');
        }

        public function issueToken(): string
        {
            return $this->accessToken();
        }
    };

    // The Google client is handed the decoded key, so no temporary file is created.
    expect(fn () => $sender->issueToken())->toThrow(PushSendException::class);
    expect($captured['client_email'])->toBe('push@achilles-fixture-project.iam.gserviceaccount.com')
        ->and($captured['private_key'])->toBe(serviceAccountFixture()['private_key']);
});

it('rejects invalid JSON without echoing the value', function () {
    config(['mobile_push.credentials_json' => '{"type":"service_account","private_key":"FIXTURE-LEAK-MARKER"']);

    $sender = new FirebasePushSender;

    expect($sender->configured())->toBeFalse()
        ->and($sender->configurationProblem())->toBe('FIREBASE_SERVICE_ACCOUNT_JSON is not valid JSON.')
        ->and($sender->configurationProblem())->not->toContain('FIXTURE-LEAK-MARKER');

    try {
        $sender->send(new MobilePushDevice, new MobilePushDelivery);
        $this->fail('A sender without credentials must refuse to send.');
    } catch (PushSendException $error) {
        expect($error->retryable)->toBeFalse()
            ->and($error->getMessage())->toBe('FIREBASE_SERVICE_ACCOUNT_JSON is not valid JSON.')
            ->and($error->getMessage())->not->toContain('FIXTURE-LEAK-MARKER');
    }
});

it('rejects a key that is missing required fields, or is not a service account', function () {
    // Missing private_key: nothing to sign with.
    config(['mobile_push.credentials_json' => json_encode(serviceAccountFixture(['private_key' => '']))]);
    expect((new FirebasePushSender)->configurationProblem())
        ->toBe('FIREBASE_SERVICE_ACCOUNT_JSON is missing project_id, client_email, private_key.');

    // The Android client google-services.json is not a server credential.
    config(['mobile_push.credentials_json' => json_encode(['type' => 'unknown', 'project_id' => 'achilles-fixture-project'])]);
    expect((new FirebasePushSender)->configurationProblem())
        ->toBe('FIREBASE_SERVICE_ACCOUNT_JSON is not a service-account key.');

    // A private_key that is not a PEM block is the wrong file.
    config(['mobile_push.credentials_json' => json_encode(serviceAccountFixture(['private_key' => 'not-a-pem']))]);
    expect((new FirebasePushSender)->configurationProblem())
        ->toBe('FIREBASE_SERVICE_ACCOUNT_JSON does not contain a PEM private key.');
});

it('rejects a credential file that cannot be read or parsed', function () {
    config(['mobile_push.credentials' => storage_path('framework/testing/missing-service-account.json')]);
    expect((new FirebasePushSender)->configurationProblem())
        ->toBe('GOOGLE_APPLICATION_CREDENTIALS is not a readable file.');

    $path = serviceAccountFile(serviceAccountFixture(['client_email' => '']));
    config(['mobile_push.credentials' => $path]);
    expect((new FirebasePushSender)->configurationProblem())
        ->toBe('GOOGLE_APPLICATION_CREDENTIALS is missing project_id, client_email, private_key.');
});

it('explains what to set when credentials are missing entirely', function () {
    expect((new FirebasePushSender)->configured())->toBeFalse();
    expect((new FirebasePushSender)->configurationProblem())
        ->toBe('Set FIREBASE_SERVICE_ACCOUNT_JSON, or GOOGLE_APPLICATION_CREDENTIALS for local development.');
});

it('reports push as disabled and keeps the reason out of the credential', function () {
    config(['mobile_push.enabled' => false, 'mobile_push.credentials_json' => json_encode(serviceAccountFixture())]);

    $sender = new FirebasePushSender;

    expect($sender->configured())->toBeFalse()
        ->and($sender->configurationProblem())->toBe('MOBILE_PUSH_ENABLED is not true.');
});

it('names the variable to fix on the worker console without printing secrets', function () {
    config(['mobile_push.credentials_json' => '{"private_key":"FIXTURE-LEAK-MARKER"']);

    $this->artisan('mobile:send-push')
        ->expectsOutputToContain('FIREBASE_SERVICE_ACCOUNT_JSON is not valid JSON.')
        ->doesntExpectOutputToContain('FIXTURE-LEAK-MARKER')
        ->assertSuccessful();
});

it('never stores credential material in a delivery record', function () {
    config(['mobile_push.credentials_json' => '{"private_key":"FIXTURE-LEAK-MARKER"']);

    $device = credentialDevice(User::factory()->create(['role' => 'super_admin', 'is_active' => true]));
    $delivery = MobilePushDelivery::create([
        'device_id' => $device->id,
        'personal_access_token_id' => $device->personal_access_token_id,
        'event_key' => 'test:leak',
        'kind' => 'test',
        'status' => 'pending',
        'available_at' => now(),
    ]);

    try {
        (new FirebasePushSender)->send($device, $delivery);
    } catch (PushSendException $error) {
        $delivery->update(['status' => 'failed', 'last_error' => $error->getMessage()]);
    }

    expect($delivery->fresh()->last_error)->toBe('FIREBASE_SERVICE_ACCOUNT_JSON is not valid JSON.')
        ->and((string) $delivery->fresh()->last_error)->not->toContain('FIXTURE-LEAK-MARKER');
});
