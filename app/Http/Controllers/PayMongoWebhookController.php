<?php

namespace App\Http\Controllers;

use App\Services\OrderService;
use App\Services\PayMongoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PayMongoWebhookController extends Controller
{
    public function __construct(
        protected PayMongoService $payMongoService,
        protected OrderService $orderService
    ) {}

    public function handle(Request $request)
    {
        $rawPayload = $request->getContent();
        $signatureHeader = $request->header('Paymongo-Signature');

        if (!$this->payMongoService->verifyWebhookSignature($rawPayload, $signatureHeader)) {
            Log::warning('PayMongo webhook: signature verification failed.');
            return response('Invalid signature', 401);
        }

        $event = json_decode($rawPayload, true);
        $eventType = $event['data']['attributes']['type'] ?? null;
        $resource = $event['data']['attributes']['data'] ?? [];

        try {
            match ($eventType) {
                'checkout_session.payment.paid' => $this->handlePaymentPaid($resource),
                'checkout_session.payment.failed' => $this->handlePaymentFailed($resource),
                default => Log::info("PayMongo webhook: unhandled event type '{$eventType}'"),
            };
        } catch (\Throwable $e) {
            // Per PayMongo's guidance: still return 200 once signature is
            // valid, even if internal processing fails — log it and
            // resolve manually rather than causing PayMongo to retry-storm.
            Log::error('PayMongo webhook processing failed: ' . $e->getMessage());
        }

        return response('OK', 200);
    }

    private function handlePaymentPaid(array $resource): void
    {
        $checkoutSessionId = $resource['id'] ?? null;

        $paymentMethodUsed = $resource['attributes']['payments'][0]['attributes']['source']['type']
            ?? 'unknown';

        if ($checkoutSessionId) {
            $this->orderService->confirmPayment($checkoutSessionId, $paymentMethodUsed);
        }
    }

    private function handlePaymentFailed(array $resource): void
    {
        $checkoutSessionId = $resource['id'] ?? null;

        if ($checkoutSessionId) {
            $this->orderService->markPaymentFailed($checkoutSessionId);
        }
    }
}