<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;

class PayMongoService
{
    protected string $baseUrl = 'https://api.paymongo.com/v1';

    /**
     * Create a hosted Checkout Session for an order.
     * Returns ['id' => ..., 'checkout_url' => ...]
     */
    public function createCheckoutSession(Order $order, string $successUrl, string $cancelUrl): array
    {
        $lineItems = $order->items->map(function ($item) {
            return [
                'name'     => $item->variant->product->product_name . " ({$item->variant->size}/{$item->variant->color})",
                'amount'   => (int) round($item->price * 100), // PayMongo expects centavos
                'currency' => 'PHP',
                'quantity' => $item->quantity,
            ];
        })->values()->all();

        $response = Http::withBasicAuth(config('services.paymongo.secret_key'), '')
            ->asJson()
            ->post("{$this->baseUrl}/checkout_sessions", [
                'data' => [
                    'attributes' => [
                        'line_items'           => $lineItems,
                        'payment_method_types' => ['card', 'gcash', 'grab_pay', 'paymaya'],
                        'success_url'          => $successUrl,
                        'cancel_url'           => $cancelUrl,
                        'send_email_receipt'   => false,
                        'reference_number'     => (string) $order->order_id,
                        'description'          => "Order #{$order->order_id} — Achilles",
                    ],
                ],
            ]);

        if ($response->failed()) {
            throw new \RuntimeException(
                'PayMongo checkout session creation failed: ' . $response->body()
            );
        }

        $data = $response->json('data');

        return [
            'id'           => $data['id'],
            'checkout_url' => $data['attributes']['checkout_url'],
        ];
    }

    /**
     * Verify a webhook payload actually came from PayMongo.
     *
     * Header format: "t=<timestamp>,te=<test_signature>,li=<live_signature>"
     * Signature = HMAC-SHA256("{timestamp}.{raw_payload}", webhook_secret)
     */
    public function verifyWebhookSignature(string $rawPayload, ?string $signatureHeader): bool
    {
        if (!$signatureHeader) {
            return false;
        }

        $parts = [];
        foreach (explode(',', $signatureHeader) as $pair) {
            [$key, $value] = array_pad(explode('=', $pair, 2), 2, null);
            $parts[$key] = $value;
        }

        if (empty($parts['t']) || (empty($parts['te']) && empty($parts['li']))) {
            return false;
        }

        $expectedSignature = hash_hmac(
            'sha256',
            "{$parts['t']}.{$rawPayload}",
            config('services.paymongo.webhook_secret')
        );

        // Match against whichever mode signature is present (test or live)
        foreach (['te', 'li'] as $mode) {
            if (!empty($parts[$mode]) && hash_equals($expectedSignature, $parts[$mode])) {
                return true;
            }
        }

        return false;
    }
}