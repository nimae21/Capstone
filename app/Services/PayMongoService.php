<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class PayMongoService
{
    protected string $baseUrl = 'https://api.paymongo.com/v1';

    /**
     * Create a hosted Checkout Session for an order.
     * Returns ['id' => ..., 'checkout_url' => ...]
     */
    public function createCheckoutSession(Order $order, string $successUrl, string $cancelUrl, ?string $idempotencyKey = null): array
    {
        $lineItems = $order->items->map(function ($item) {
            return [
                'name' => $item->variant->product->product_name." ({$item->variant->size}/{$item->variant->color})",
                'amount' => (int) round($item->price * 100), // PayMongo expects centavos
                'currency' => 'PHP',
                'quantity' => $item->quantity,
            ];
        })->values()->all();

        $response = $this->client()
            ->withHeaders(['Idempotency-Key' => $idempotencyKey ?? 'checkout-order-'.$order->order_id])
            ->asJson()
            ->post("{$this->baseUrl}/checkout_sessions", [
                'data' => [
                    'attributes' => [
                        'billing' => [
                            'name' => $order->full_name,
                            'email' => $order->user->email,
                            'phone' => $order->phone_number,
                            'address' => [
                                'line1' => $order->street,
                                'line2' => $order->barangay,
                                'city' => $order->city,
                                'state' => $order->province,
                                'postal_code' => $order->postal_code,
                                'country' => 'PH',
                            ],
                        ],
                        'line_items' => $lineItems,
                        'payment_method_types' => ['card', 'gcash', 'grab_pay', 'paymaya'],
                        'success_url' => $successUrl,
                        'cancel_url' => $cancelUrl,
                        'send_email_receipt' => false,
                        'reference_number' => (string) $order->order_id,
                        'description' => "Order #{$order->order_id} — Achilles",
                    ],
                ],
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Payment provider could not create checkout session.');
        }

        $data = $response->json('data');

        return [
            'id' => $data['id'],
            'checkout_url' => $data['attributes']['checkout_url'],
            'payment_intent_id' => $data['attributes']['payment_intent']['id'] ?? null,
        ];
    }

    private function client(): PendingRequest
    {
        return Http::withBasicAuth(config('services.paymongo.secret_key'), '')
            ->acceptJson()->asJson()->connectTimeout(3)->timeout(10);
    }

    public function retrieveCheckoutSession(string $sessionId): array
    {
        return $this->client()->get("{$this->baseUrl}/checkout_sessions/{$sessionId}")
            ->throw()->json('data');
    }

    public function paidPayment(array $session): ?array
    {
        return collect($session['attributes']['payments'] ?? [])
            ->first(fn ($payment) => ($payment['attributes']['status'] ?? null) === 'paid'
                && str_starts_with($payment['id'] ?? '', 'pay_'));
    }

    public function expireCheckoutSession(string $sessionId): array
    {
        $session = $this->retrieveCheckoutSession($sessionId);
        if ($this->paidPayment($session) || ($session['attributes']['status'] ?? null) === 'expired') {
            return $session;
        }

        try {
            $this->client()->post("{$this->baseUrl}/checkout_sessions/{$sessionId}/expire")->throw();
        } catch (RequestException $e) {
            // Payment may have won the race with expiry.
            $session = $this->retrieveCheckoutSession($sessionId);
            if ($this->paidPayment($session) || ($session['attributes']['status'] ?? null) === 'expired') {
                return $session;
            }
            throw $e;
        }

        $session = $this->retrieveCheckoutSession($sessionId);
        if (! $this->paidPayment($session) && ($session['attributes']['status'] ?? null) !== 'expired') {
            throw new \RuntimeException('Checkout session expiration was not confirmed.');
        }

        return $session;
    }

    public function refundPayment(string $paymentId, int $amount, string $requestKey, int $orderId): array
    {
        return $this->client()->withHeaders(['Idempotency-Key' => $requestKey])
            ->post("{$this->baseUrl}/refunds", [
                'data' => ['attributes' => [
                    'payment_id' => $paymentId,
                    'amount' => $amount,
                    'reason' => 'others',
                    'notes' => "Cancellation of Achilles order #{$orderId}",
                ]],
            ])->throw()->json('data');
    }

    /**
     * Verify a webhook payload actually came from PayMongo.
     *
     * Header format: "t=<timestamp>,te=<test_signature>,li=<live_signature>"
     * Signature = HMAC-SHA256("{timestamp}.{raw_payload}", webhook_secret)
     */
    public function verifyWebhookSignature(string $rawPayload, ?string $signatureHeader): bool
    {
        if (! $signatureHeader || ! config('services.paymongo.webhook_secret')) {
            return false;
        }

        $parts = [];
        foreach (explode(',', $signatureHeader) as $pair) {
            [$key, $value] = array_pad(explode('=', $pair, 2), 2, null);
            $parts[trim($key)] = trim($value ?? '');
        }

        if (empty($parts['t']) || ! ctype_digit($parts['t']) || (empty($parts['te']) && empty($parts['li']))) {
            return false;
        }

        $tolerance = max(30, (int) config('services.paymongo.webhook_tolerance', 300));
        if (abs(time() - (int) $parts['t']) > $tolerance) {
            return false;
        }

        $expectedSignature = hash_hmac(
            'sha256',
            "{$parts['t']}.{$rawPayload}",
            config('services.paymongo.webhook_secret')
        );

        // Match against whichever mode signature is present (test or live)
        foreach (['te', 'li'] as $mode) {
            if (! empty($parts[$mode]) && hash_equals($expectedSignature, $parts[$mode])) {
                return true;
            }
        }

        return false;
    }
}
