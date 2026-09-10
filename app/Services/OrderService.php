<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Exceptions\EmptyCartException;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\InvalidOrderTransitionException;
use App\Exceptions\OrderNotCancellableException;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use App\Enums\SaleType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class OrderService
{
    public function __construct(
        protected StockService $stockService,
        protected PayMongoService $payMongoService
    ) {}

    /**
     * Create a Pending order from the user's cart WITHOUT deducting stock
     * or touching the cart's status — those only happen once PayMongo
     * confirms payment via webhook. Stock availability is checked here
     * as an early, friendly validation, but the authoritative check
     * happens again at confirmation time under a row lock.
     *
     * @throws EmptyCartException
     * @throws InsufficientStockException
     */
    public function createPendingOrderFromCart(User $user, int $addressId): Order
    {
        $cart = Cart::with('items.variant.stocks', 'items.variant.product')
            ->where('user_id', $user->id)
            ->where('status', 0)
            ->firstOrFail();

        if ($cart->items->isEmpty()) {
            throw new EmptyCartException('Your cart is empty.');
        }

        foreach ($cart->items as $item) {
            if (!$this->stockService->hasStock($item->variant, $item->quantity)) {
                throw new InsufficientStockException(
                    "Insufficient stock for {$item->variant->product->product_name} ({$item->variant->size}/{$item->variant->color})."
                );
            }
        }

        $address = $user->addresses()->findOrFail($addressId);

        return DB::transaction(function () use ($cart, $address) {
            $total = $cart->items->sum(fn ($item) => $item->price * $item->quantity);

            $order = Order::create([
                'user_id'        => $cart->user_id,
                'sale_type'      => SaleType::Online,
                'total_amount'   => $total,
                'status'         => OrderStatus::Pending,
                'payment_method' => null, // unknown until PayMongo confirms
                'full_name'      => $address->full_name,
                'phone_number'   => $address->phone_number,
                'street'         => $address->street,
                'barangay'       => $address->barangay,
                'city'           => $address->city,
                'province'       => $address->province,
                'postal_code'    => $address->postal_code,
                'latitude'       => $address->latitude,
                'longitude'      => $address->longitude,
            ]);

            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id'           => $order->order_id,
                    'product_variant_id' => $item->product_variant_id,
                    'quantity'           => $item->quantity,
                    'price'              => $item->price,
                ]);
            }

            return $order->fresh('items');
        });
    }

    /**
     * All lifecycle writes lock the order first, then its payment. The payment
     * status check and stock deduction commit together, including duplicate events.
     */
    public function confirmPayment(
        string $checkoutSessionId,
        string $paymentMethodUsed,
        string $paymongoPaymentId,
        ?int $amount = null,
        string $currency = 'PHP'
    ): void {
        if (!str_starts_with($paymongoPaymentId, 'pay_')) {
            throw new \RuntimeException('Missing PayMongo payment ID.');
        }
        $hint = Payment::forCheckoutSession($checkoutSessionId)->firstOrFail();
        $refundPaymentId = DB::transaction(function () use ($hint, $checkoutSessionId, $paymentMethodUsed, $paymongoPaymentId, $amount, $currency) {
            $order = Order::whereKey($hint->order_id)->lockForUpdate()->firstOrFail();
            $payment = Payment::whereKey($hint->getKey())->lockForUpdate()->firstOrFail();
            if ($order->sale_type !== SaleType::Online) {
                throw new \RuntimeException('PayMongo confirmation does not belong to an online order.');
            }
            if ($currency !== 'PHP' || ($amount !== null && $amount !== (int) round($order->total_amount * 100))) {
                throw new \RuntimeException('Payment amount or currency does not match the order.');
            }
            if ($payment->paymongo_payment_id && $payment->paymongo_payment_id !== $paymongoPaymentId) {
                throw new \RuntimeException('Conflicting PayMongo payment IDs for one checkout session.');
            }
            if ($payment->checkout_session_id !== $checkoutSessionId && $payment->status !== 'completed') {
                // A delayed paid event from a retired session must close its replacement.
                $replacement = $this->payMongoService->expireCheckoutSession($payment->checkout_session_id);
                if ($this->payMongoService->paidPayment($replacement)) {
                    throw new \RuntimeException('Multiple paid checkout sessions require manual payment reconciliation.');
                }
                $payment->previous_checkout_session_ids = array_values(array_unique(array_merge(
                    $payment->previous_checkout_session_ids ?? [], [$payment->checkout_session_id]
                )));
                $payment->checkout_session_id = $checkoutSessionId;
            }
            $payment->paymongo_payment_id = $paymongoPaymentId;

            if ($payment->status === 'completed') {
                $payment->save();
                if ($order->status === OrderStatus::Cancelled) {
                    $this->prepareRefund($order, $payment);
                    return $payment->getKey();
                }
                return null;
            }

            if ($order->status !== OrderStatus::Pending && $order->status !== OrderStatus::Cancelled) {
                throw new \RuntimeException('Unexpected order state during payment confirmation.');
            }
            // Cancelled orders are never fulfilled or charged stock by a late event.
            if ($order->status === OrderStatus::Pending && !$payment->refund_status) {
                foreach ($order->items()->with('variant')->orderBy('product_variant_id')->get() as $item) {
                    $this->stockService->deduct($item->variant, $item->quantity, $item->order_item_id);
                }
            }

            $payment->fill([
                'status' => 'completed', 'method' => $paymentMethodUsed, 'payment_date' => now(),
            ])->save();
            $order->payment_method = $paymentMethodUsed;

            if ($order->status === OrderStatus::Cancelled || $payment->refund_status) {
                $order->status = OrderStatus::Cancelled;
                $order->save();
                $this->prepareRefund($order, $payment);
                return $payment->getKey();
            }

            $order->status = OrderStatus::Paid;
            $order->save();
            $order->user->carts()->where('status', 0)->update(['status' => 1]);
            Log::info('PayMongo payment confirmed', $this->paymentContext($payment));
            return null;
        });

        if ($refundPaymentId) {
            $this->requestRefund($refundPaymentId);
        }
    }

    public function markPaymentFailed(string $checkoutSessionId, string $status = 'failed'): void
    {
        if (!in_array($status, ['failed', 'expired'], true)) {
            throw new \InvalidArgumentException('Invalid unsuccessful payment status.');
        }
        $hint = Payment::forCheckoutSession($checkoutSessionId)->firstOrFail();
        DB::transaction(function () use ($hint, $checkoutSessionId, $status) {
            $order = Order::whereKey($hint->order_id)->lockForUpdate()->firstOrFail();
            $payment = Payment::whereKey($hint->getKey())->lockForUpdate()->firstOrFail();
            // An old failed attempt cannot overwrite its replacement or a paid order.
            if ($order->sale_type === SaleType::Online && $order->status === OrderStatus::Pending
                && $payment->checkout_session_id === $checkoutSessionId && !$payment->refund_status
                && in_array($payment->status, ['pending', 'failed', 'expired'], true)) {
                $payment->update(['status' => $payment->status === 'expired' ? 'expired' : $status]);
                Log::info('PayMongo payment attempt unsuccessful', array_merge(
                    $this->paymentContext($payment), ['payment_status' => $payment->status]
                ));
            }
        });
    }

    public function refreshCheckoutPayment(Order $order): void
    {
        $payment = $order->payment;
        if ($order->sale_type !== SaleType::Online || $order->status !== OrderStatus::Pending
            || !$payment?->checkout_session_id || $payment->status === 'completed' || $payment->refund_status) {
            return;
        }
        $session = $this->payMongoService->retrieveCheckoutSession($payment->checkout_session_id);
        $paid = $this->payMongoService->paidPayment($session);
        if ($paid) {
            $this->confirmPayment($session['id'], $paid['attributes']['source']['type'] ?? 'unknown',
                $paid['id'], $paid['attributes']['amount'], $paid['attributes']['currency']);
        } elseif (($session['attributes']['status'] ?? null) === 'expired') {
            $this->markPaymentFailed($session['id'], 'expired');
        } elseif (!empty($session['attributes']['payment_intent']['attributes']['last_payment_error'])
            || collect($session['attributes']['payments'] ?? [])->contains(
                fn ($attempt) => ($attempt['attributes']['status'] ?? null) === 'failed')) {
            $this->markPaymentFailed($session['id']);
        }
    }

    public function retryCheckout(Order $order, ?string $expectedSessionId): ?string
    {
        return DB::transaction(function () use ($order, $expectedSessionId) {
            $current = Order::whereKey($order->getKey())->lockForUpdate()->firstOrFail();
            $payment = $current->payment()->lockForUpdate()->first();
            if ($current->sale_type !== SaleType::Online || $current->status !== OrderStatus::Pending
                || !$payment || $payment->refund_status
                || !in_array($payment->status, ['pending', 'failed', 'expired'], true)) {
                throw new \RuntimeException('This order is not eligible for payment retry.');
            }
            // A repeated form submission must not expire a newly opened checkout.
            if ($payment->checkout_session_id !== $expectedSessionId) {
                return null;
            }
            if ($payment->checkout_session_id) {
                $session = $this->payMongoService->expireCheckoutSession($payment->checkout_session_id);
                if ($paid = $this->payMongoService->paidPayment($session)) {
                    $this->confirmPayment($session['id'], $paid['attributes']['source']['type'] ?? 'unknown',
                        $paid['id'], $paid['attributes']['amount'], $paid['attributes']['currency']);
                    return null;
                }
            }
            foreach ($current->items()->with('variant.stocks')->get() as $item) {
                if (!$this->stockService->hasStock($item->variant, $item->quantity)) {
                    throw new InsufficientStockException('An item is no longer in stock. Please contact support or cancel this order.');
                }
            }
            $session = $this->payMongoService->createCheckoutSession($current,
                route('checkout.success', $current->order_id), route('checkout.cancel', $current->order_id));
            if ($session['id'] === $payment->checkout_session_id
                || in_array($session['id'], $payment->previous_checkout_session_ids ?? [], true)
                || (!empty($session['payment_intent_id']) && $session['payment_intent_id'] === $payment->paymongo_payment_intent_id)) {
                throw new \RuntimeException('PayMongo retry returned a previously used payment attempt.');
            }
            $previous = $payment->previous_checkout_session_ids ?? [];
            if ($payment->checkout_session_id) {
                $previous[] = $payment->checkout_session_id;
            }
            $payment->update([
                'previous_checkout_session_ids' => array_values(array_unique($previous)),
                'checkout_session_id' => $session['id'],
                'paymongo_payment_intent_id' => $session['payment_intent_id'] ?? null,
                'status' => 'pending', 'method' => 'pending',
            ]);
            Log::info('PayMongo checkout retry created', array_merge($this->paymentContext($payment), [
                'previous_checkout_session_id' => $expectedSessionId,
                'payment_intent_id' => $payment->paymongo_payment_intent_id,
            ]));
            return $session['checkout_url'];
        });
    }

    public function cancel(Order $order): Order
    {
        $refundPaymentId = DB::transaction(function () use ($order) {
            $current = Order::whereKey($order->getKey())->lockForUpdate()->firstOrFail();
            $payment = $current->payment()->lockForUpdate()->first();
            $alreadyCancelled = $current->status === OrderStatus::Cancelled;
            if (!$alreadyCancelled && !$current->status->isCancellable()) {
                throw new OrderNotCancellableException("Order #{$current->order_id} can no longer be cancelled.");
            }
            if ($current->sale_type === SaleType::Online && $current->status === OrderStatus::Paid && !$payment) {
                throw new OrderNotCancellableException('The payment record is missing. Please verify payment before cancellation.');
            }
            $restoreStock = $current->status === OrderStatus::Paid;
            $onlinePayment = $current->sale_type === SaleType::Online
                && $payment && $payment->method !== 'cash_pos';

            if ($onlinePayment) {
                if ($payment->status !== 'completed' && $payment->status !== 'cancelled' && $payment->checkout_session_id) {
                    // Verify/expire while holding the order lock. If payment wins,
                    // record it and refund rather than wrongly treating it as unpaid.
                    $session = $this->payMongoService->expireCheckoutSession($payment->checkout_session_id);
                    $paid = $this->payMongoService->paidPayment($session);
                    if ($paid) {
                        $this->recordPaymentForCancellation($current, $payment, $paid);
                    }
                } elseif ($payment->status === 'completed' && !$payment->paymongo_payment_id) {
                    if (!$payment->checkout_session_id) {
                        throw new OrderNotCancellableException('Payment needs manual verification before a refund can be requested.');
                    }
                    $session = $this->payMongoService->retrieveCheckoutSession($payment->checkout_session_id);
                    $paid = $this->payMongoService->paidPayment($session);
                    if (!$paid) {
                        throw new OrderNotCancellableException('PayMongo has not confirmed this payment. Please verify it before cancelling.');
                    }
                    $this->recordPaymentForCancellation($current, $payment, $paid);
                }
                if ($restoreStock && $payment->status !== 'completed') {
                    throw new OrderNotCancellableException('The paid order and provider payment do not agree. Manual verification is required.');
                }
                if ($payment->status === 'completed') {
                    $this->prepareRefund($current, $payment);
                } else {
                    $payment->update(['status' => 'cancelled']);
                }
            }

            // The locked status is the once-only stock-restoration guard.
            if ($restoreStock) {
                foreach ($current->items()->orderBy('product_variant_id')->get() as $item) {
                    $this->stockService->restore($item);
                }
            }
            if (!$alreadyCancelled) {
                $current->update(['status' => OrderStatus::Cancelled]);
            }
            Log::info('Order cancellation recorded', $payment
                ? $this->paymentContext($payment) : ['order_id' => $current->order_id]);

            return $onlinePayment && $payment->refund_status === 'pending' ? $payment->getKey() : null;
        });

        // The refund intent/key is durable BEFORE contacting the external API.
        if ($refundPaymentId) {
            $this->requestRefund($refundPaymentId);
        }
        return $order->fresh('payment');
    }

    private function recordPaymentForCancellation(Order $order, Payment $payment, array $paid): void
    {
        $attributes = $paid['attributes'];
        if (($attributes['currency'] ?? '') !== 'PHP'
            || ($attributes['amount'] ?? null) !== (int) round($order->total_amount * 100)) {
            throw new OrderNotCancellableException('Payment amount or currency needs manual verification.');
        }
        if ($payment->paymongo_payment_id && $payment->paymongo_payment_id !== $paid['id']) {
            throw new OrderNotCancellableException('Conflicting payment references need manual verification.');
        }
        $method = $attributes['source']['type'] ?? 'unknown';
        $payment->update([
            'paymongo_payment_id' => $paid['id'], 'status' => 'completed',
            'method' => $method, 'payment_date' => $payment->payment_date ?? now(),
        ]);
        $order->update(['payment_method' => $method]);
    }

    private function prepareRefund(Order $order, Payment $payment): void
    {
        if ($payment->refund_status !== null) {
            return;
        }
        $payment->update([
            'refund_status' => 'pending',
            'refund_amount' => (int) round($order->total_amount * 100),
            'refund_request_key' => (string) \Illuminate\Support\Str::uuid(),
            'refund_requested_at' => now(),
            'refund_error' => null,
        ]);
    }

    private function requestRefund(int $paymentId): void
    {
        $hint = Payment::findOrFail($paymentId);
        $failure = DB::transaction(function () use ($hint) {
            Order::whereKey($hint->order_id)->lockForUpdate()->firstOrFail();
            $payment = Payment::whereKey($hint->getKey())->lockForUpdate()->firstOrFail();
            if ($payment->refund_status !== 'pending' || $payment->paymongo_refund_id) {
                return null;
            }
            // Provider keys expire after 24h. Never blindly submit an ambiguous
            // request after that window: it needs provider-side reconciliation.
            if (!$payment->refund_requested_at || $payment->refund_requested_at->lte(now()->subHours(23))) {
                $payment->update(['refund_error' => 'Refund outcome needs manual verification before another request.']);
                Log::warning('PayMongo refund requires reconciliation', $this->paymentContext($payment));
                return null;
            }
            try {
                $refund = $this->payMongoService->refundPayment(
                    $payment->paymongo_payment_id, $payment->refund_amount,
                    $payment->refund_request_key, $payment->order_id,
                );
                $this->applyRefund($payment, $refund);
                return null;
            } catch (\Throwable $e) {
                $status = $e instanceof \Illuminate\Http\Client\RequestException ? $e->response->status() : null;
                $definitiveFailure = $status !== null && $status >= 400 && $status < 500
                    && !in_array($status, [408, 409, 429], true);
                $payment->update([
                    'refund_status' => $definitiveFailure ? 'failed' : 'pending',
                    'refund_error' => $definitiveFailure
                        ? 'PayMongo rejected the refund. Please contact support for review.'
                        : 'Refund outcome is not confirmed. Retry safely or contact support.',
                ]);
                Log::error('PayMongo refund request failed', array_merge($this->paymentContext($payment), [
                    'http_status' => $status, 'exception' => get_class($e),
                ]));
                // Commit the error state, then propagate transient failures for webhook retry.
                return $definitiveFailure ? null : $e;
            }
        });
        if ($failure instanceof \Throwable) {
            throw new \RuntimeException('Refund confirmation is temporarily unavailable. Please retry.', 0, $failure);
        }
    }

    public function syncRefund(array $refund): void
    {
        $paymentId = $refund['attributes']['payment_id'] ?? null;
        if (!$paymentId || !str_starts_with($refund['id'] ?? '', 'ref_')) {
            throw new \RuntimeException('Refund event is missing its payment or refund ID.');
        }
        $hint = Payment::where('paymongo_payment_id', $paymentId)->firstOrFail();
        DB::transaction(function () use ($hint, $refund) {
            $order = Order::whereKey($hint->order_id)->lockForUpdate()->firstOrFail();
            $payment = Payment::whereKey($hint->getKey())->lockForUpdate()->firstOrFail();
            if ($order->sale_type !== SaleType::Online || $payment->method === 'cash_pos') {
                throw new \RuntimeException('Refund does not belong to a PayMongo order.');
            }
            // External partial refunds must not be displayed as a full order refund.
            $expected = $payment->refund_amount ?? (int) round($order->total_amount * 100);
            if (($refund['attributes']['amount'] ?? null) !== $expected) {
                Log::warning('PayMongo partial/unmatched refund requires manual review', array_merge(
                    $this->paymentContext($payment), ['refund_id' => $refund['id']]
                ));
                return;
            }
            $this->applyRefund($payment, $refund);
        });
    }

    private function applyRefund(Payment $payment, array $refund): void
    {
        $attributes = $refund['attributes'] ?? [];
        if (!str_starts_with($refund['id'] ?? '', 'ref_')
            || ($attributes['payment_id'] ?? null) !== $payment->paymongo_payment_id) {
            throw new \RuntimeException('Refund response does not match the payment.');
        }
        if ($payment->paymongo_refund_id && $payment->paymongo_refund_id !== $refund['id']) {
            Log::warning('Additional PayMongo refund requires review', array_merge(
                $this->paymentContext($payment), ['incoming_refund_id' => $refund['id']]
            ));
            return;
        }
        $status = match ($attributes['status'] ?? '') {
            'pending', 'processing' => 'pending',
            'succeeded' => 'refunded',
            'failed' => 'failed',
            default => throw new \RuntimeException('Unknown PayMongo refund status.'),
        };
        if ($payment->refund_amount !== null && ($attributes['amount'] ?? null) !== $payment->refund_amount) {
            throw new \RuntimeException('Refund amount does not match the request.');
        }
        $updatedAt = (int) ($attributes['updated_at'] ?? $attributes['created_at'] ?? 0);
        if ($payment->refund_status === 'refunded'
            || ($payment->refund_updated_at && $updatedAt < $payment->refund_updated_at)
            || ($payment->refund_status === 'failed' && $status === 'pending')) {
            return;
        }
        $payment->update([
            'paymongo_refund_id' => $refund['id'],
            'refund_status' => $status,
            'refund_amount' => $attributes['amount'],
            'refund_updated_at' => $updatedAt,
            'refund_error' => $status === 'failed' ? 'PayMongo could not complete the refund. Please contact support.' : null,
        ]);
        Log::info('PayMongo refund state updated', array_merge(
            $this->paymentContext($payment), ['refund_status' => $status]
        ));
    }

    private function paymentContext(Payment $payment): array
    {
        return [
            'order_id' => $payment->order_id,
            'checkout_session_id' => $payment->checkout_session_id,
            'paymongo_payment_id' => $payment->paymongo_payment_id,
            'refund_id' => $payment->paymongo_refund_id,
        ];
    }

    public function updateStatus(Order $order, OrderStatus $newStatus): Order
    {
        if ($newStatus === OrderStatus::Cancelled) {
            return $this->cancel($order);
        }
        return DB::transaction(function () use ($order, $newStatus) {
            $current = Order::whereKey($order->getKey())->lockForUpdate()->firstOrFail();
            $payment = $current->payment()->lockForUpdate()->first();
            if (!$current->status->canTransitionTo($newStatus) || $payment?->refund_status) {
                throw new InvalidOrderTransitionException(
                    "Cannot change order #{$current->order_id} from '{$current->status->label()}' to '{$newStatus->label()}'."
                );
            }
            $current->update(['status' => $newStatus]);
            return $current->fresh();
        });
    }
    /**
 * Create and immediately complete a walk-in POS sale. Unlike web
 * checkout, this is synchronous and cash-only — no pending state,
 * no webhook. Stock is deducted immediately since payment is
 * confirmed at the point of sale.
 *
 * @param array $items [['product_variant_id' => int, 'quantity' => int, 'price' => float], ...]
 * @throws InsufficientStockException
 */
public function createPosSale(array $items, User $cashier): Order
{
    if (empty($items)) {
        throw new \InvalidArgumentException('Cannot create a sale with no items.');
    }

    return DB::transaction(function () use ($items, $cashier) {
        $total = 0;

        foreach ($items as $item) {
            $variant = \App\Models\ProductVariant::findOrFail($item['product_variant_id']);

            if (!$this->stockService->hasStock($variant, $item['quantity'])) {
                throw new InsufficientStockException(
                    "Insufficient stock for {$variant->product->product_name} ({$variant->size}/{$variant->color})."
                );
            }

            $total += $item['price'] * $item['quantity'];
        }

        $order = Order::create([
            'user_id'        => $cashier->id,
            'sale_type'      => SaleType::Pos,
            'total_amount'   => $total,
            'status'         => OrderStatus::Paid,
            'payment_method' => 'cash_pos',
            'full_name'      => 'Walk-in Customer',
            'phone_number'   => 'N/A',
            'street'         => 'In-Store Purchase',
            'barangay'       => 'N/A',
            'city'           => 'N/A',
            'province'       => 'N/A',
            'postal_code'    => 'N/A',
        ]);

        foreach ($items as $item) {
            $variant = \App\Models\ProductVariant::findOrFail($item['product_variant_id']);

            $orderItem = OrderItem::create([
                'order_id'           => $order->order_id,
                'product_variant_id' => $item['product_variant_id'],
                'quantity'           => $item['quantity'],
                'price'              => $item['price'],
            ]);

            $this->stockService->deduct($variant, $item['quantity'], $orderItem->order_item_id);
        }

        Payment::create([
            'order_id'     => $order->order_id,
            'method'       => 'cash_pos',
            'status'       => 'completed',
            'payment_date' => now(),
        ]);

        return $order->fresh('items.variant.product');
    });
}
}