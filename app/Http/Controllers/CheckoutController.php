<?php

namespace App\Http\Controllers;

use App\Exceptions\EmptyCartException;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\OrderNotCancellableException;
use App\Http\Requests\PlaceOrderRequest;
use App\Models\Cart;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

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

    public function placeOrder(PlaceOrderRequest $request)
    {
        try {
            $order = $this->orderService->placeOrderFromCart(
                Auth::user(),
                $request->validated('address_id'),
                $request->validated('payment_method'),
            );

            return redirect()
                ->route('orders.show', $order->order_id)
                ->with('success', 'Order placed successfully! Your payment has been processed.');

        } catch (EmptyCartException|InsufficientStockException $e) {
            return back()->withInput()->with('error', $e->getMessage());
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
        if ($order->user_id != auth()->id()) {
            abort(403);
        }

        try {
            $this->orderService->cancel($order);
            return back()->with('success', 'Order cancelled successfully.');
        } catch (OrderNotCancellableException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}