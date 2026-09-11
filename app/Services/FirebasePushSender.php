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
    public function configured(): bool
    {
        $path = config('mobile_push.credentials');

        return (bool) config('mobile_push.enabled')
            && (bool) preg_match('/^[a-z][a-z0-9-]{4,61}[a-z0-9]$/', (string) config('mobile_push.project_id'))
            && is_string($path) && is_file($path) && is_readable($path);
    }

    public function send(MobilePushDevice $device, MobilePushDelivery $delivery): string
    {
        if (! $this->configured()) {
            throw new PushSendException(false, 'not_configured');
        }
        $project = config('mobile_push.project_id');
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
        return 'mobile-fcm-auth:'.hash('sha256', config('mobile_push.project_id').'|'.config('mobile_push.credentials').'|'.@filemtime(config('mobile_push.credentials')));
    }

    protected function accessToken(): string
    {
        return Cache::remember($this->cacheKey(), 3000, function () {
            try {
                $credentials = new ServiceAccountCredentials(
                    ['https://www.googleapis.com/auth/firebase.messaging'],
                    config('mobile_push.credentials')
                );
                $token = $credentials->fetchAuthToken(HttpHandlerFactory::build(new Client(['timeout' => 10, 'connect_timeout' => 5]), false));
                if (empty($token['access_token'])) {
                    throw new \RuntimeException();
                }

                return $token['access_token'];
            } catch (\Throwable) {
                throw new PushSendException(true, 'firebase_auth_failed');
            }
        });
    }
}