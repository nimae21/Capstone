<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
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

    return view('checkout.index', compact('cart'));
}

public function placeOrder(Request $request)
{
    // ✅ Validate FIRST
    $request->validate([
        'full_name' => 'required|string|max:255',
        'street' => 'required|string|max:255',
        'barangay' => 'required|string|max:255', // FIXED spelling
        'city' => 'required|string|max:255',
        'province' => 'required|string|max:255',
        'postal_code' => 'required|string|max:20',
        'phone_number' => 'required|string|max:20',
    ]);

    $cart = Cart::with('items.variant.stocks')
        ->where('user_id', auth()->id())
        ->where('status', 0)
        ->firstOrFail();

    DB::beginTransaction();

    try {
        $total = 0;

        // ✅ Validate stock again
        foreach ($cart->items as $item) {
            $stock = $item->variant->stocks()->latest()->first();

            if (!$stock || $item->quantity > $stock->quantity) {
                throw new \Exception('Stock issue detected for product.');
            }

            $total += $item->price * $item->quantity;
        }

        // ✅ Create Order (FIXED fields)
        $order = Order::create([
            'user_id' => auth()->id(),
            'total_amount' => $total,
            'status' => 'pending',
            'full_name' => $request->full_name, // FIXED
            'street' => $request->street,
            'barangay' => $request->barangay, // FIXED
            'city' => $request->city,
            'province' => $request->province,
            'postal_code' => $request->postal_code,
            'phone_number' => $request->phone_number,
        ]);

        // ✅ Create Order Items + Deduct Stock
        foreach ($cart->items as $item) {
            $stock = $item->variant->stocks()->latest()->first();

            OrderItem::create([
                'order_id' => $order->order_id,
                'product_variant_id' => $item->product_variant_id,
                'quantity' => $item->quantity,
                'price' => $item->price,
            ]);

            $stock->decrement('quantity', $item->quantity);
        }

        // ✅ Mark cart as completed
        $cart->update(['status' => 1]);

        DB::commit();

        return redirect()->route('orders.show', $order->order_id)
    ->with('success', 'Order placed successfully!');
            
    } catch (\Exception $e) {
        DB::rollBack();

        return back()
            ->withInput() // 🔥 VERY IMPORTANT (keeps form data)
            ->with('error', $e->getMessage());
    }
}

public function myOrders()
{
    $orders = Order::where('user_id', auth()->id())
        ->orderBy('created_at', 'desc')
        ->get();

    return view('orders.index', compact('orders'));
}
}
