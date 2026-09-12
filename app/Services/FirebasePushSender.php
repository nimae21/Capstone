<?php

namespace App\Services;

use App\Exceptions\PushSendException;
use App\Models\MobilePushDelivery;
use App\Models\MobilePushDevice;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\HttpHandler\HttpHandlerFactory;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class FirebasePushSender
{
    /**
     * Fields a Google service-account key must carry before we try to use it.
     * Checked up-front so a bad variable fails once, with a clear reason,
     * instead of a provider error on every retry.
     */
    private const REQUIRED_FIELDS = ['project_id', 'client_email', 'private_key'];

    /** Longest reason string we ever store in `mobile_push_deliveries.last_error`. */
    private const MAX_PROBLEM_LENGTH = 100;

    /** Credential source resolved for this process. */
    private ?array $resolved = null;

    /**
     * True when a usable service-account key and project id are available.
     * Never throws: callers use it to report status, so a bad variable must
     * degrade to "not configured" rather than break the endpoint.
     */
    public function configured(): bool
    {
        return $this->resolve()['problem'] === null;
    }

    /**
     * Why the sender is not usable, in operator terms. The string never
     * contains credential material - only the name of the variable to fix.
     */
    public function configurationProblem(): string
    {
        return (string) ($this->resolve()['problem'] ?? '');
    }

    public function send(MobilePushDevice $device, MobilePushDelivery $delivery): string
    {
        $resolved = $this->resolve();

        if ($resolved['problem'] !== null) {
            // A missing or malformed variable is not worth retrying.
            throw new PushSendException(false, $resolved['problem']);
        }
        $project = $resolved['project_id'];
        [$title, $body, $route] = $this->content($delivery);
        $response = Http::withToken($this->accessToken())->acceptJson()->connectTimeout(5)->timeout(15)
            ->post("https://fcm.googleapis.com/v1/projects/{$project}/messages:send", [
                'message' => [
                    'token' => $device->token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => [
                        'type' => $delivery->kind,
                        'order_id' => (string) ($delivery->order_id ?? ''),
                        'notification_id' => (string) ($delivery->notification_id ?? ''),
                        'route' => (string) ($route ?? ''),
                    ],
                    'android' => [
                        'priority' => 'HIGH',
                        'ttl' => '86400s',
                        'notification' => [
                            'channel_id' => 'orders',
                            'icon' => 'ic_stat_achilles',
                            'sound' => 'default',
                            'tag' => $delivery->event_key,
                        ],
                    ],
                ],
            ]);
        if ($response->successful()) {
            return 'sent';
        }
        $details = collect($response->json('error.details', []));
        if ($details->contains(fn ($detail) => ($detail['errorCode'] ?? '') === 'UNREGISTERED')) {
            return 'unregistered';
        }
        if ($response->status() === 401) {
            Cache::forget($this->cacheKey());
        }
        throw new PushSendException($response->status() === 401 || $response->status() === 429 || $response->serverError(), 'fcm_http_'.$response->status());
    }

    /**
     * Alert copy comes from the stored payload so the backend - never the
     * phone - decides what an event means. Legacy order-paid deliveries with
     * no payload keep their original copy.
     *
     * @return array{0: string, 1: string, 2: ?string}
     */
    private function content(MobilePushDelivery $delivery): array
    {
        $data = is_array($delivery->data) ? $delivery->data : [];
        if (! empty($data['title'])) {
            return [
                (string) $data['title'],
                (string) ($data['body'] ?? ''),
                $data['route'] ?? null,
            ];
        }

        return match ($delivery->kind) {
            'order_paid' => ['New paid order', "Order #{$delivery->order_id} is ready to review.", "/tabs/orders/{$delivery->order_id}"],
            'test' => ['Achilles notifications are working', 'Your phone received this test notification.', null],
            default => ['Achilles Super Admin', 'You have a new system alert.', null],
        };
    }

    private function cacheKey(): string
    {
        $account = $this->resolve()['account'] ?? [];

        // Identifies the credential without repeating any of it: rotating the
        // key changes private_key_id, so a token minted for the old key is
        // never reused.
        return 'mobile-fcm-auth:'.hash('sha256', implode('|', [
            (string) ($this->resolve()['project_id'] ?? ''),
            (string) ($account['private_key_id'] ?? ''),
            (string) ($account['client_email'] ?? ''),
            (string) ($account['project_id'] ?? ''),
        ]));
    }

    protected function accessToken(): string
    {
        return Cache::remember($this->cacheKey(), 3000, function () {
            $resolved = $this->resolve();

            if ($resolved['account'] === null) {
                throw new PushSendException(false, (string) $resolved['problem']);
            }

            try {
                $credentials = $this->serviceAccountCredentials($resolved['account']);
                $token = $credentials->fetchAuthToken(HttpHandlerFactory::build(new Client(['timeout' => 10, 'connect_timeout' => 5]), false));
                if (empty($token['access_token'])) {
                    throw new \RuntimeException;
                }

                return $token['access_token'];
            } catch (\Throwable) {
                // Google's exceptions can carry request context, so only the
                // fact that authentication failed is ever surfaced.
                throw new PushSendException(true, 'firebase_auth_failed');
            }
        });
    }

    /**
     * The Google client accepts the decoded key directly, so no temporary file
     * is ever written for the environment-variable credential source.
     *
     * @param  array<string, mixed>  $account
     */
    protected function serviceAccountCredentials(array $account): ServiceAccountCredentials
    {
        return new ServiceAccountCredentials(
            ['https://www.googleapis.com/auth/firebase.messaging'],
            $account
        );
    }

    /**
     * Resolves the credential once per process.
     *
     * @return array{account: ?array<string, mixed>, project_id: ?string, problem: ?string}
     */
    private function resolve(): array
    {
        return $this->resolved ??= $this->detect();
    }

    /**
     * @return array{account: ?array<string, mixed>, project_id: ?string, problem: ?string}
     */
    private function detect(): array
    {
        if (! config('mobile_push.enabled')) {
            return $this->unusable('MOBILE_PUSH_ENABLED is not true.');
        }

        $credential = $this->serviceAccount();

        if ($credential['account'] === null) {
            return $this->unusable($credential['problem']);
        }

        // FIREBASE_PROJECT_ID stays the source of truth when it is set; the
        // service account's own project is the fallback so a container host
        // only has to store one variable.
        $projectId = $this->projectId($credential['account']);

        if ($projectId === null) {
            return $this->unusable('FIREBASE_PROJECT_ID is missing or malformed, and the service account carries no project_id.');
        }

        return ['account' => $credential['account'], 'project_id' => $projectId, 'problem' => null];
    }

    /**
     * Prefers the whole-JSON variable (Railway, containers) and falls back to
     * the credential file path used for local development.
     *
     * @return array{account: ?array<string, mixed>, problem: ?string}
     */
    private function serviceAccount(): array
    {
        $json = config('mobile_push.credentials_json');

        if (is_string($json) && trim($json) !== '') {
            return $this->decodeAccount(trim($json), 'FIREBASE_SERVICE_ACCOUNT_JSON');
        }

        $path = config('mobile_push.credentials');

        if (! is_string($path) || trim($path) === '') {
            return $this->unusable('Set FIREBASE_SERVICE_ACCOUNT_JSON, or GOOGLE_APPLICATION_CREDENTIALS for local development.');
        }

        $path = trim($path);

        if (! is_file($path) || ! is_readable($path)) {
            return $this->unusable('GOOGLE_APPLICATION_CREDENTIALS is not a readable file.');
        }

        $contents = @file_get_contents($path);

        if ($contents === false) {
            return $this->unusable('GOOGLE_APPLICATION_CREDENTIALS could not be read.');
        }

        return $this->decodeAccount($contents, 'GOOGLE_APPLICATION_CREDENTIALS');
    }

    /**
     * Decodes and validates a service-account key. The contents are never
     * echoed: every failure returns a fixed reason that names the variable.
     *
     * @return array{account: ?array<string, mixed>, problem: ?string}
     */
    private function decodeAccount(string $json, string $source): array
    {
        $account = json_decode($json, true);

        if (! is_array($account)) {
            // A variable pasted with real line breaks inside the private key is
            // not valid JSON, and that is the one mistake worth repairing.
            $account = json_decode($this->escapeRawLineBreaks($json), true);
        }

        if (! is_array($account)) {
            return $this->unusable($source.' is not valid JSON.');
        }

        if (($account['type'] ?? null) !== 'service_account') {
            return $this->unusable($source.' is not a service-account key.');
        }

        foreach (self::REQUIRED_FIELDS as $field) {
            if (! is_string($account[$field] ?? null) || trim((string) $account[$field]) === '') {
                return $this->unusable($source.' is missing '.implode(', ', self::REQUIRED_FIELDS).'.');
            }
        }

        if (! str_contains($account['private_key'], 'PRIVATE KEY-----')) {
            return $this->unusable($source.' does not contain a PEM private key.');
        }

        return ['account' => $account, 'problem' => null];
    }

    /**
     * Escapes line breaks and tabs that were pasted inside JSON strings, which
     * is what makes an otherwise correct key fail to decode.
     */
    private function escapeRawLineBreaks(string $json): string
    {
        return (string) preg_replace_callback(
            '/"((?:[^"\\\\]|\\\\.)*)"/s',
            fn (array $match): string => '"'.str_replace(
                ["\r\n", "\n", "\r", "\t"],
                ['\\n', '\\n', '\\n', '\\t'],
                $match[1],
            ).'"',
            $json,
        );
    }

    /** @param array<string, mixed> $account */
    private function projectId(array $account): ?string
    {
        $configured = config('mobile_push.project_id');

        if (is_string($configured) && $this->validProjectId($configured)) {
            return trim($configured);
        }

        $fromKey = $account['project_id'] ?? null;

        return is_string($fromKey) && $this->validProjectId($fromKey) ? trim($fromKey) : null;
    }

    /** FCM rejects malformed project ids, so catch them before the round trip. */
    private function validProjectId(string $value): bool
    {
        return (bool) preg_match('/^[a-z][a-z0-9-]{4,61}[a-z0-9]$/', trim($value));
    }

    /**
     * @return array{account: null, project_id: null, problem: string}
     */
    private function unusable(string $problem): array
    {
        return [
            'account' => null,
            'project_id' => null,
            'problem' => substr($problem, 0, self::MAX_PROBLEM_LENGTH),
        ];
    }
}
