<?php

namespace App\Http\Controllers;

use App\Models\Payment;
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
        if (strlen($rawPayload) > 1048576) {
            return response()->json(['message' => 'Webhook payload too large'], 413);
        }
        if (! $this->payMongoService->verifyWebhookSignature($rawPayload, $request->header('Paymongo-Signature'))) {
            Log::warning('PayMongo webhook: signature verification failed.');

            return response()->json(['message' => 'Invalid signature'], 401);
        }
        $event = json_decode($rawPayload, true);
        $eventType = $event['data']['attributes']['type'] ?? null;
        $resource = $event['data']['attributes']['data'] ?? null;
        if (! is_string($eventType) || ! is_array($resource)) {
            return response()->json(['message' => 'Invalid webhook payload'], 400);
        }
        $context = ['event_id' => $event['data']['id'] ?? null, 'event_type' => $eventType,
            'checkout_session_id' => null, 'paymongo_payment_id' => null, 'refund_id' => null, 'order_id' => null];
        try {
            if ($eventType === 'checkout_session.payment.paid') {
                $sessionId = $resource['id'] ?? '';
                $paid = $this->payMongoService->paidPayment($resource);
                $context['checkout_session_id'] = $sessionId;
                $context['paymongo_payment_id'] = $paid['id'] ?? null;
                $context['order_id'] = Payment::forCheckoutSession($sessionId)->value('order_id');
                if (! str_starts_with($sessionId, 'cs_') || ! $paid
                    || ! isset($paid['attributes']['amount'], $paid['attributes']['currency'])) {
                    throw new \RuntimeException('Paid event is missing payment details.');
                }
                $this->orderService->confirmPayment(
                    $sessionId, $paid['attributes']['source']['type'] ?? 'unknown', $paid['id'],
                    $paid['attributes']['amount'], $paid['attributes']['currency']
                );
            } elseif ($eventType === 'payment.failed') {
                $context['paymongo_payment_id'] = $resource['id'] ?? null;
                $intentId = $resource['attributes']['payment_intent_id'] ?? null;
                $context['payment_intent_id'] = $intentId;
                $payment = $intentId ? Payment::where('paymongo_payment_intent_id', $intentId)->first() : null;
                if ($payment) {
                    $context['order_id'] = $payment->order_id;
                    $context['checkout_session_id'] = $payment->checkout_session_id;
                    // A signed failed event needs only a local write. Do not operate on
                    // the provider attempt while its authentication page is finishing.
                    $this->orderService->markPaymentFailed($payment->checkout_session_id);
                } else {
                    // Legacy/unrelated source payments cannot be safely mapped by customer or amount.
                    // The order page also reconciles directly with its saved Checkout Session.
                    Log::warning('Unmatched PayMongo failed payment', $context);
                }
            } elseif ($eventType === 'checkout_session.payment.failed') {
                $context['checkout_session_id'] = $resource['id'] ?? '';
                $this->orderService->markPaymentFailed($context['checkout_session_id']);
            } elseif (in_array($eventType, ['payment.refunded', 'payment.refund.updated'], true)) {
                // Accept a refund resource, or refunds embedded in a payment resource.
                $refunds = str_starts_with($resource['id'] ?? '', 'ref_')
                    ? [$resource] : ($resource['attributes']['refunds'] ?? []);
                if (! $refunds) {
                    throw new \RuntimeException('Refund event contains no refund resource.');
                }
                foreach ($refunds as $refund) {
                    if (! isset($refund['attributes'])) {
                        $refund = ['id' => $refund['id'] ?? null, 'attributes' => $refund];
                    }
                    $refund['attributes']['payment_id'] ??= str_starts_with($resource['id'] ?? '', 'pay_')
                        ? $resource['id'] : null;
                    $context['refund_id'] = $refund['id'] ?? null;
                    $context['paymongo_payment_id'] = $refund['attributes']['payment_id'] ?? null;
                    $payment = Payment::where('paymongo_payment_id', $context['paymongo_payment_id'])->first();
                    $context['order_id'] = $payment?->order_id;
                    $context['checkout_session_id'] = $payment?->checkout_session_id;
                    $this->orderService->syncRefund($refund);
                }
            } else {
                Log::info('PayMongo webhook ignored', $context);
            }
        } catch (\Throwable $e) {
            Log::error('PayMongo webhook processing failed', array_merge($context, [
                'exception' => get_class($e),
                'reason' => get_class($e) === \RuntimeException::class ? $e->getMessage() : null,
                // No raw API response, billing data, or credentials in logs.
            ]));

            return response()->json(['message' => 'Payment processing temporarily unavailable'], 503);
        }

        return response()->json(['message' => 'OK']);
    }
}
