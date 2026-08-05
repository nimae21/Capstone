<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminOrderController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Display Orders
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $orders = Order::with(['user', 'items'])
            ->latest()
            ->paginate(20);

        $stats = [
            'total_orders'  => Order::count(),
            'pending'       => Order::where('status', 'pending')->count(),
            'completed'     => Order::where('status', 'completed')->count(),
            'total_revenue' => Order::where('status', 'completed')->sum('total_amount'),
        ];

        return view('admin.orders.index', compact('orders', 'stats'));
    }

    /*
    |--------------------------------------------------------------------------
    | Order Details
    |--------------------------------------------------------------------------
    */
    public function show(Order $order)
    {
        $order->load([
            'user',
            'items.variant.product',
            'payment'
        ]);

        return view('admin.orders.show', compact('order'));
    }

    /*
    |--------------------------------------------------------------------------
    | Update Order Status
    |--------------------------------------------------------------------------
    */
    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,paid,shipped,completed,cancelled',
        ]);

        try {

            DB::transaction(function () use ($validated, $order) {

                $oldStatus = $order->status;

                // Load relationships once
                $order->load('items.variant.product');

                /*
                |--------------------------------------------------------------------------
                | Deduct Inventory
                | Only when changing from PAID -> SHIPPED
                |--------------------------------------------------------------------------
                */
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
                            throw new \Exception(
                                "Stock not found for {$item->variant->product->product_name}."
                            );
                        }

                        if ($stock->remaining_quantity < $item->quantity) {
                            throw new \Exception(
                                "Not enough stock for {$item->variant->product->product_name}."
                            );
                        }

                        // Deduct inventory
                        $stock->decrement(
                            'remaining_quantity',
                            $item->quantity
                        );

                        // Record stock movement
                        StockMovement::create([
                            'stock_id'      => $stock->stock_id,
                            'order_item_id' => $item->order_item_id,
                            'quantity'      => $item->quantity,
                            'type'          => 'out',
                        ]);
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Update Status
                |--------------------------------------------------------------------------
                */
                $order->update([
                    'status' => $validated['status']
                ]);
            });

            return back()->with(
                'success',
                'Order status updated successfully.'
            );

        } catch (\Exception $e) {

            return back()->with(
                'error',
                $e->getMessage()
            );

        }
    }
}