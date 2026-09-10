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
        $cart = Cart::with('items.variant.product.images')
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

            $order = \Illuminate\Support\Facades\DB::transaction(function () use ($order, $session) {
                $current = Order::whereKey($order->getKey())->lockForUpdate()->firstOrFail();
                Payment::create([
                    'order_id' => $current->order_id,
                    'checkout_session_id' => $session['id'],
                    'paymongo_payment_intent_id' => $session['payment_intent_id'] ?? null,
                    'method' => 'pending',
                    'status' => 'pending',
                ]);
                return $current;
            });

            // A cancellation can arrive while the checkout API call is running.
            // Persist its session, then close it instead of opening a cancelled order.
            if ($order->status === \App\Enums\OrderStatus::Cancelled) {
                try {
                    $this->orderService->cancel($order);
                    return redirect()->route('orders.show', $order->order_id);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('Cancelled order session could not be closed', [
                        'order_id' => $order->order_id, 'checkout_session_id' => $session['id'],
                        'exception' => get_class($e),
                    ]);
                    return redirect()->route('orders.show', $order->order_id)
                        ->with('error', 'The checkout session could not be closed. Retry cancellation below.');
                }
            }
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

        // A provider return URL is a GET navigation, not permission to refund.
        // Show the order's protected cancellation form instead.
        return redirect()->route('orders.show', $order->order_id)
            ->with('error', 'Checkout was closed. Your order is not cancelled. Check its payment status below; you can retry payment or cancel the order.');
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
        $order = Order::with(['items.variant.product.images', 'payment'])
            ->where('order_id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        try {
            $this->orderService->refreshCheckoutPayment($order);
            $order->refresh();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('PayMongo checkout status refresh unavailable', [
                'order_id' => $order->order_id, 'checkout_session_id' => $order->payment?->checkout_session_id,
                'exception' => get_class($e),
            ]);
            session()->flash('error', 'Payment status could not be verified yet. Please try again shortly.');
        }
        return view('orders.show', compact('order'));
    }

    public function retryPayment(\Illuminate\Http\Request $request, Order $order)
    {
        abort_if($order->user_id != auth()->id(), 403);
        $data = $request->validate(['checkout_session_id' => ['present', 'nullable', 'string', 'max:255']]);
        try {
            $url = $this->orderService->retryCheckout($order, $data['checkout_session_id']);
            return $url ? redirect()->away($url)
                : redirect()->route('orders.show', $order->order_id)
                    ->with('success', 'Payment status changed. Review the current order status below.');
        } catch (InsufficientStockException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('PayMongo checkout retry unavailable', [
                'order_id' => $order->order_id, 'checkout_session_id' => $order->payment?->checkout_session_id,
                'exception' => get_class($e),
            ]);
            return back()->with('error', 'Payment retry could not be started. Check the order status and try again shortly.');
        }
    }

    public function cancelOrder(Order $order)
    {
        if ($order->user_id != auth()->id()) {
            abort(403);
        }

        try {
            $updated = $this->orderService->cancel($order);
            $label = $updated->payment?->refund_label;
            return back()->with('success', 'Order cancelled.' . ($label ? ' '.$label.'.' : ''));
        } catch (OrderNotCancellableException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Order cancellation could not finish', [
                'order_id' => $order->order_id, 'exception' => get_class($e),
            ]);
            return back()->with('error', 'Cancellation or refund could not be confirmed. Check the order below and retry if available.');
        }
    }
}