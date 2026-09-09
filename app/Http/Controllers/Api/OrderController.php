<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Exceptions\InvalidOrderTransitionException;
use App\Exceptions\OrderNotCancellableException;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function __construct(protected OrderService $orderService) {}

    public function index(Request $request)
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(['all', 'pending', 'paid', 'shipped', 'completed', 'cancelled'])],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
        $query = Order::with(['user', 'items.variant.product'])->where('sale_type', 'online');
        $status = $filters['status'] ?? 'paid';
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        return response()->json($query->latest()->orderByDesc('order_id')->paginate(20)
            ->through(fn (Order $order) => $this->serialize($order)));
    }

    public function show(Order $order)
    {
        abort_unless($order->sale_type?->value === 'online', 404);
        return response()->json($this->serialize($order->load(['user', 'items.variant.product'])));
    }

    // Kept for previously installed mobile clients.
    public function pending()
    {
        return response()->json(Order::with(['user', 'items.variant.product'])
            ->where('sale_type', 'online')->where('status', OrderStatus::Paid)->latest()->get()
            ->map(fn (Order $order) => $this->serialize($order)));
    }

    public function confirm(Order $order)
    {
        return $this->transition($order, OrderStatus::Shipped);
    }

    public function updateStatus(Request $request, Order $order)
    {
        // These are the two actions displayed on the website's order detail page.
        $validated = $request->validate(['status' => ['required', Rule::in(['shipped', 'completed'])]]);
        return $this->transition($order, OrderStatus::from($validated['status']));
    }

    private function transition(Order $order, OrderStatus $status)
    {
        try {
            $updated = DB::transaction(function () use ($order, $status) {
                // Read current state under a lock: a repeated tap must not repeat an action.
                $current = Order::whereKey($order->getKey())->lockForUpdate()->firstOrFail();
                abort_unless($current->sale_type?->value === 'online', 404);
                return $this->orderService->updateStatus($current, $status);
            });

            return response()->json([
                'message' => $status === OrderStatus::Shipped ? 'Order marked as shipped.' : 'Order marked as delivered.',
                'order' => $this->serialize($updated->load(['user', 'items.variant.product'])),
            ]);
        } catch (InvalidOrderTransitionException|OrderNotCancellableException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    private function serialize(Order $order): array
    {
        return [
            'id' => $order->order_id,
            'customer' => $order->user?->full_name ?? $order->full_name ?? 'Customer',
            'recipient' => $order->full_name,
            'phone' => $order->phone_number,
            'total' => $order->total_amount,
            'created_at' => $order->created_at?->toIso8601String(),
            'status' => $order->status->label(),
            'status_key' => $order->status->value,
            'payment_method' => $order->payment_method,
            'address' => implode(', ', array_filter([$order->street, $order->barangay, $order->city, $order->province, $order->postal_code])),
            'items' => $order->items->map(fn ($item) => [
                'product' => $item->variant?->product?->product_name ?? 'Unavailable product',
                'size' => $item->variant?->size,
                'color' => $item->variant?->color,
                'quantity' => $item->quantity,
                'price' => $item->price,
            ]),
        ];
    }
}