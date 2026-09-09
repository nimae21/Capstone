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
        if (!$this->configured()) throw new PushSendException(false, 'not_configured');
        $isOrder = $delivery->kind === 'order_paid';
        $project = config('mobile_push.project_id');
        $response = Http::withToken($this->accessToken())->acceptJson()->connectTimeout(5)->timeout(15)
            ->post("https://fcm.googleapis.com/v1/projects/{$project}/messages:send", [
                'message' => [
                    'token' => $device->token,
                    'notification' => [
                        'title' => $isOrder ? 'New paid order' : 'Achilles notifications are working',
                        'body' => $isOrder ? "Order #{$delivery->order_id} is ready to review." : 'Your phone received this test notification.',
                    ],
                    'data' => [
                        'type' => $delivery->kind,
                        'order_id' => $isOrder ? (string) $delivery->order_id : '',
                        'notification_id' => (string) $delivery->id,
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
        if ($response->successful()) return 'sent';
        $details = collect($response->json('error.details', []));
        if ($details->contains(fn ($detail) => ($detail['errorCode'] ?? '') === 'UNREGISTERED')) return 'unregistered';
        if ($response->status() === 401) Cache::forget($this->cacheKey());
        throw new PushSendException($response->status() === 401 || $response->status() === 429 || $response->serverError(), 'fcm_http_'.$response->status());
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
                if (empty($token['access_token'])) throw new \RuntimeException();
                return $token['access_token'];
            } catch (\Throwable) {
                throw new PushSendException(true, 'firebase_auth_failed');
            }
        });
    }
}
