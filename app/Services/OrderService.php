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
        protected StockService $stockService
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
     * Called by the PayMongo webhook once a checkout session's payment
     * succeeds. Idempotent — safe to call multiple times for the same
     * session (PayMongo may retry webhook delivery).
     */
    public function confirmPayment(string $checkoutSessionId, string $paymentMethodUsed): void
    {
        $payment = Payment::where('checkout_session_id', $checkoutSessionId)->first();

        if (!$payment) {
            Log::warning("PayMongo webhook: no Payment found for checkout session {$checkoutSessionId}");
            return;
        }

        if ($payment->status === 'completed') {
            return; // already processed — webhook retry, ignore safely
        }

        DB::transaction(function () use ($payment, $paymentMethodUsed) {
            $order = $payment->order()->with('items.variant')->first();

            foreach ($order->items as $item) {
                $this->stockService->deduct($item->variant, $item->quantity, $item->order_item_id);
            }

            $payment->update([
                'status'       => 'completed',
                'method'       => $paymentMethodUsed,
                'payment_date' => now(),
            ]);

            $order->update([
                'status'         => OrderStatus::Paid,
                'payment_method' => $paymentMethodUsed,
            ]);

            // Only NOW is it safe to mark the cart as ordered
            $order->user->carts()->where('status', 0)->update(['status' => 1]);
        });
    }

    /**
     * Called when PayMongo reports a failed/expired checkout session.
     */
    public function markPaymentFailed(string $checkoutSessionId): void
    {
        Payment::where('checkout_session_id', $checkoutSessionId)
            ->where('status', '!=', 'completed')
            ->update(['status' => 'failed']);
    }

    public function cancel(Order $order): Order
    {
        if (!$order->status->isCancellable()) {
            throw new OrderNotCancellableException(
                "Order #{$order->order_id} can no longer be cancelled (current status: {$order->status->label()})."
            );
        }

        return DB::transaction(function () use ($order) {
            $order->load('items');

            // Only restore stock if it was actually deducted (i.e. order was Paid)
            if ($order->status === OrderStatus::Paid) {
                foreach ($order->items as $item) {
                    $this->stockService->restore($item);
                }
            }

            $order->update(['status' => OrderStatus::Cancelled]);

            return $order->fresh();
        });
    }

    public function updateStatus(Order $order, OrderStatus $newStatus): Order
    {
        if (!$order->status->canTransitionTo($newStatus)) {
            throw new InvalidOrderTransitionException(
                "Cannot change order #{$order->order_id} status from '{$order->status->label()}' to '{$newStatus->label()}'."
            );
        }

        if ($newStatus === OrderStatus::Cancelled) {
            return $this->cancel($order);
        }

        $order->update(['status' => $newStatus]);

        return $order->fresh();
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