<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Exceptions\InvalidOrderTransitionException;
use App\Exceptions\OrderNotCancellableException;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Services\OrderService;

class AdminOrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    public function index(Request $request)
{
    $query = Order::with(['user', 'items']);

    if ($request->filled('sale_type')) {
        $query->where('sale_type', $request->sale_type);
    }

    $orders = $query->latest()->paginate(20);

    $stats = [
        'total_orders'  => Order::count(),
        'pending'       => Order::where('status', OrderStatus::Pending)->count(),
        'completed'     => Order::where('status', OrderStatus::Completed)->count(),
        'total_revenue' => Order::where('status', OrderStatus::Completed)->sum('total_amount'),
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
        }
    }
}