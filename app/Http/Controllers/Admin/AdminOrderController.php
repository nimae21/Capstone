<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Exceptions\InvalidOrderTransitionException;
use App\Exceptions\OrderNotCancellableException;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminOrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    public function index(Request $request)
    {
        $query = Order::query()
            ->select('order_id', 'user_id', 'total_amount', 'status', 'created_at')
            ->with('user:id,first_name,last_name,email')
            ->withCount('items');

        if ($request->filled('sale_type')) {
            $query->where('sale_type', $request->sale_type);
        }

        $orders = $query->orderByDesc('created_at')->orderByDesc('order_id')->paginate(20)->withQueryString();
        $summary = Order::query()
            ->selectRaw(
                'COUNT(*) as total_orders,
                 COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) as pending,
                 COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) as completed,
                 COALESCE(SUM(CASE WHEN status = ? THEN total_amount ELSE 0 END), 0) as total_revenue',
                [OrderStatus::Pending->value, OrderStatus::Completed->value, OrderStatus::Completed->value],
            )
            ->first();
        $stats = [
            'total_orders' => (int) $summary->total_orders,
            'pending' => (int) $summary->pending,
            'completed' => (int) $summary->completed,
            'total_revenue' => (float) $summary->total_revenue,
        ];

        return view('admin.orders.index', compact('orders', 'stats'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'items.variant.product', 'payment']);

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order)
    {
        try {
            $newStatus = OrderStatus::from($request->validated('status'));
            $this->orderService->updateStatus($order, $newStatus);

            return back()->with('success', 'Order status updated successfully.');

        } catch (InvalidOrderTransitionException|OrderNotCancellableException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Admin order transition could not finish', [
                'order_id' => $order->order_id, 'exception' => get_class($e),
            ]);

            return back()->with('error', 'Payment processing could not be confirmed. Check the order and refund status before retrying.');
        }
    }
}
