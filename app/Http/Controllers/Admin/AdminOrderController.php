<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\StockMovement;

class AdminOrderController extends Controller
{
    // List all orders
    public function index()
    {
        $orders = Order::with(['user', 'items'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total_orders' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'completed' => Order::where('status', 'completed')->count(),
            'total_revenue' => Order::where('status', 'completed')->sum('total_amount'),
        ];

        return view('admin.orders.index', compact('orders', 'stats'));
    }

    // Show order details
    public function show(Order $order)
    {
        $order->load(['user', 'items.variant', 'payment']);
        return view('admin.orders.show', compact('order'));
    }

    // Update order status
   public function updateStatus(Request $request, Order $order)
{
    $validated = $request->validate([
        'status' => 'required|in:pending,paid,shipped,completed,cancelled',
    ]);

    try {

        DB::transaction(function () use ($validated, $order) {

    $oldStatus = $order->status;

    // Only deduct stock the first time the order becomes shipped
    if (
    $oldStatus === 'paid' &&
    $validated['status'] === 'shipped'
) {

        foreach ($order->items as $item) {

            $stock = $item->variant
                ->stocks()
                ->latest('stock_id')
                ->first();

            if (!$stock) {
                throw new \Exception("Stock not found.");
            }

            if ($stock->quantity < $item->quantity) {
                throw new \Exception(
                    "Not enough stock for {$item->variant->product->product_name}"
                );
            }

            // Deduct stock
            $stock->decrement('quantity', $item->quantity);

            // Record movement
            StockMovement::create([
                'stock_id' => $stock->stock_id,
                'order_item_id' => $item->order_item_id,
                'quantity' => $item->quantity,
                'type' => 'out',
            ]);
        }
    }

    // Update order status AFTER inventory operations succeed
    $order->update([
        'status' => $validated['status']
    ]);

});
            


        return back()->with('success', 'Order status updated.');

    } catch (\Exception $e) {

        return back()->with('error', $e->getMessage());

    }
}
}
