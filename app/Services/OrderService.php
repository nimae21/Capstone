<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Exceptions\EmptyCartException;
use App\Exceptions\InvalidOrderTransitionException;
use App\Exceptions\OrderNotCancellableException;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        protected StockService $stockService
    ) {}

    /**
     * @throws EmptyCartException
     * @throws \App\Exceptions\InsufficientStockException
     */
    public function placeOrderFromCart(User $user, int $addressId, string $paymentMethod): Order
    {
        $cart = Cart::with('items.variant.stocks', 'items.variant.product')
            ->where('user_id', $user->id)
            ->where('status', 0)
            ->firstOrFail();

        if ($cart->items->isEmpty()) {
            throw new EmptyCartException('Your cart is empty.');
        }

        $address = $user->addresses()->findOrFail($addressId);

        return DB::transaction(function () use ($cart, $address, $paymentMethod) {

            // Authoritative check happens again inside StockService::deduct()
            // under a row lock; this loop is an early, friendlier bail-out
            // before we've written anything to the database.
            foreach ($cart->items as $item) {
                if (!$this->stockService->hasStock($item->variant, $item->quantity)) {
                    throw new \App\Exceptions\InsufficientStockException(
                        "Insufficient stock for {$item->variant->product->product_name} ({$item->variant->size}/{$item->variant->color})."
                    );
                }
            }

            $total = $cart->items->sum(fn ($item) => $item->price * $item->quantity);

            $order = Order::create([
                'user_id'        => $cart->user_id,
                'total_amount'   => $total,
                'status'         => OrderStatus::Pending,
                'payment_method' => $paymentMethod,
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
                $orderItem = OrderItem::create([
                    'order_id'           => $order->order_id,
                    'product_variant_id' => $item->product_variant_id,
                    'quantity'           => $item->quantity,
                    'price'              => $item->price,
                ]);

                $this->stockService->deduct($item->variant, $item->quantity, $orderItem->order_item_id);
            }

            Payment::create([
                'order_id'     => $order->order_id,
                'method'       => $paymentMethod,
                'status'       => 'completed',
                'payment_date' => now(),
            ]);

            $order->update(['status' => OrderStatus::Paid]);
            $cart->update(['status' => 1]);

            return $order->fresh();
        });
    }

    /**
     * @throws OrderNotCancellableException
     */
    public function cancel(Order $order): Order
    {
        if (!$order->status->isCancellable()) {
            throw new OrderNotCancellableException(
                "Order #{$order->order_id} can no longer be cancelled (current status: {$order->status->label()})."
            );
        }

        return DB::transaction(function () use ($order) {
            $order->load('items');

            foreach ($order->items as $item) {
                $this->stockService->restore($item);
            }

            $order->update(['status' => OrderStatus::Cancelled]);

            return $order->fresh();
        });
    }

    /**
     * @throws InvalidOrderTransitionException
     * @throws OrderNotCancellableException
     */
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
}