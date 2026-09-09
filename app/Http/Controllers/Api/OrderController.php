<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Exceptions\InvalidOrderTransitionException;
use App\Exceptions\OrderNotCancellableException;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;

class OrderController extends Controller
{
    public function __construct(protected OrderService $orderService) {}

    public function pending()
    {
        $orders = Order::with(['user', 'items.variant.product'])
            ->where('status', OrderStatus::Paid) // Paid = waiting for admin to ship
            ->latest()
            ->get()
            ->map(fn($order) => [
                'id'         => $order->order_id,
                'customer'   => $order->user->full_name,
                'total'      => $order->total_amount,
                'created_at' => $order->created_at->diffForHumans(),
                'status'     => $order->status->label(),
                'address'    => "{$order->street}, {$order->barangay}, {$order->city}, {$order->province}",
                'items'      => $order->items->map(fn($item) => [
                    'product'  => $item->variant->product->product_name,
                    'size'     => $item->variant->size,
                    'color'    => $item->variant->color,
                    'quantity' => $item->quantity,
                    'price'    => $item->price,
                ]),
            ]);

        return response()->json($orders);
    }

    public function confirm(Order $order)
    {
        try {
            // Paid -> Shipped is the admin's confirm action.
            // This goes through updateStatus() so the state machine
            // in OrderStatus::canTransitionTo() is always enforced —
            // same as the web admin panel does it.
            $this->orderService->updateStatus($order, OrderStatus::Shipped);

            return response()->json(['message' => 'Order marked as shipped.']);
        } catch (InvalidOrderTransitionException|OrderNotCancellableException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}