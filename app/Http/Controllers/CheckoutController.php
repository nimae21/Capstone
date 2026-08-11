<?php

namespace App\Http\Controllers;

use App\Exceptions\EmptyCartException;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\OrderNotCancellableException;
use App\Http\Requests\PlaceOrderRequest;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Payment;
use App\Services\OrderService;
use App\Services\PayMongoService;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected PayMongoService $payMongoService
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
            $order = $this->orderService->createPendingOrderFromCart(
                Auth::user(),
                $request->validated('address_id'),
            );

            $session = $this->payMongoService->createCheckoutSession(
                $order,
                route('checkout.success', $order->order_id),
                route('checkout.cancel', $order->order_id),
            );

            Payment::create([
                'order_id'            => $order->order_id,
                'checkout_session_id' => $session['id'],
                'method'              => 'pending', // real method known only after webhook
                'status'              => 'pending',
            ]);

            return redirect()->away($session['checkout_url']);

        } catch (EmptyCartException|InsufficientStockException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Customer lands here after completing (or attempting) payment on
     * PayMongo's page. The webhook — not this page — is the source of
     * truth for whether payment actually succeeded, since it can arrive
     * before or after this redirect.
     */
    public function success(Order $order)
    {
        abort_if($order->user_id != auth()->id(), 403);

        return view('checkout.success', compact('order'));
    }

    public function cancel(Order $order)
    {
        abort_if($order->user_id != auth()->id(), 403);

        return redirect()
            ->route('checkout.index')
            ->with('error', 'Payment was cancelled. Your order was not placed — please try again.');
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

    public function cancelOrder(Order $order)
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