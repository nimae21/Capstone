<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\Payment;

use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function checkout()
    {
        $cart = Cart::with('items.variant.product')
        
            ->where('user_id', auth()->id())
            ->where('status', 0)
            ->first();
            

        if (!$cart || $cart->items->isEmpty()) {
            return back()->with('error', 'Cart is empty');
        }

        $addresses = auth()->user()->addresses()->orderBy('is_default', 'desc')->get();
        return view('checkout.index', compact('cart', 'addresses'));
    }

    public function placeOrder(Request $request)
    {
        $validated = $request->validate([
    'address_id' => 'required|exists:user_addresses,address_id',
    'payment_method' => 'required|in:credit_card,debit_card,gcash,paypal,cash_on_delivery',
]);

        $cart = Cart::with('items.variant.stocks')
            ->where('user_id', auth()->id())
            ->where('status', 0)
            ->firstOrFail();

        if ($cart->items->isEmpty()) {
            return back()->with('error', 'Your cart is empty');
        }

        DB::beginTransaction();

        try {
            $total = 0;

            // Validate stock
            foreach ($cart->items as $item) {
                $stock = $item->variant->stocks()->latest()->first();

                if (!$stock || $item->quantity > $stock->quantity) {
                    throw new \Exception('Insufficient stock for ' . $item->variant->product->product_name);
                }

                $total += $item->price * $item->quantity;
            }

            $address = auth()->user()
    ->addresses()
    ->findOrFail($validated['address_id']);

$addressData = [
    'full_name' => $address->full_name,
    'phone_number' => $address->phone_number,
    'street' => $address->street,
    'barangay' => $address->barangay,
    'city' => $address->city,
    'province' => $address->province,
    'postal_code' => $address->postal_code,
    'latitude' => $address->latitude,
    'longitude' => $address->longitude,
];




            // Create Order
            $order = Order::create(array_merge([
                'user_id' => auth()->id(),
                'total_amount' => $total,
                'status' => 'pending',
                'payment_method' => $validated['payment_method'],
            ], $addressData));

            // Create Order Items & Stock Movements
            foreach ($cart->items as $item) {
                

                // Create order item
                $orderItem = OrderItem::create([
                    'order_id' => $order->order_id,
                    'product_variant_id' => $item->product_variant_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                ]);

            }

            // Create Payment Record (Mock)
            Payment::create([
                'order_id' => $order->order_id,
                'method' => $validated['payment_method'],
                'status' => 'completed', // Mock: always complete payment
                'payment_date' => now(),
            ]);

            // Update order status to paid (since we mock payment)
            $order->update(['status' => 'paid']);

            // Mark cart as completed
            $cart->update(['status' => 1]);

            DB::commit();

            return redirect()
                ->route('orders.show', $order->order_id)
                ->with('success', 'Order placed successfully! Your payment has been processed.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Failed to place order: ' . $e->getMessage());
        }
    }

    public function myOrders()
    {
        $orders = Order::where('user_id', auth()->id())
            ->with(['items', 'payment'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function show($id)
    {
        $order = Order::with(['items.variant.product', 'payment'])
            ->where('order_id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return view('orders.show', compact('order'));
    }

public function cancel(Order $order)
{
    // Make sure this order belongs to the logged in user
    if ($order->user_id != auth()->id()) {
        abort(403);
    }

    // Only pending orders can be cancelled
    if ($order->status !== 'pending') {
        return back()->with('error', 'This order can no longer be cancelled.');
    }

    $order->update([
        'status' => 'cancelled'
    ]);

    return back()->with('success', 'Order cancelled successfully.');
}
}
