<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Services\ActivityTrackingService;
use App\Services\StockService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        protected StockService $stockService,
        protected ActivityTrackingService $activityTracker
    ) {}

    /**
     * Display cart
     */
    public function index()
    {
        $cart = Cart::with([
            'items.variant.product',
            'items.variant.stocks',
        ])
            ->where('user_id', auth()->id())
            ->where('status', 0)
            ->first();

        return view('cart.index', compact('cart'));
    }

    /**
     * Add product to cart
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_variant_id' => 'required|exists:product_variants,product_variant_id',
            'quantity' => 'required|integer|min:1',
        ]);

        $variant = ProductVariant::with('product')->findOrFail($request->product_variant_id);

        $availableStock = $this->stockService->availableQuantity($variant);

        if ($availableStock <= 0) {
            return back()->with('error', 'This item is out of stock.');
        }

        if ($request->quantity > $availableStock) {
            return back()->with('error', "Only {$availableStock} item(s) available.");
        }

        $cart = Cart::firstOrCreate([
            'user_id' => auth()->id(),
            'status' => 0,
        ]);

        $item = CartItem::where('cart_id', $cart->cart_id)
            ->where('product_variant_id', $variant->product_variant_id)
            ->first();

        if ($item) {

            $newQuantity = $item->quantity + $request->quantity;

            if ($newQuantity > $availableStock) {
                return back()->with('error', "Only {$availableStock} item(s) available.");
            }

            $item->update([
                'quantity' => $newQuantity,
            ]);

        } else {

            CartItem::create([
                'cart_id' => $cart->cart_id,
                'product_variant_id' => $variant->product_variant_id,
                'quantity' => $request->quantity,
                'price' => $this->stockService->currentPrice($variant),
            ]);

        }

        $this->activityTracker->logAddToCart(auth()->user(), $variant->product);

        return redirect()
            ->route('cart.index')
            ->with('success', 'Product added to cart.');
    }

    /**
     * Increase quantity
     */
    public function increase($id)
    {
        $item = CartItem::with('cart', 'variant')->findOrFail($id);

        abort_if($item->cart->user_id != auth()->id(), 403);

        $availableStock = $this->stockService->availableQuantity($item->variant);

        if ($item->quantity >= $availableStock) {
            return back()->with('error', 'Maximum stock reached.');
        }

        $item->increment('quantity');

        return back()->with('success', 'Cart quantity updated.');
    }

    /**
     * Decrease quantity
     */
    public function decrease($id)
    {
        $item = CartItem::with('cart')->findOrFail($id);

        abort_if($item->cart->user_id != auth()->id(), 403);

        if ($item->quantity <= 1) {

            $item->delete();

            return back()->with('success', 'Item removed from cart.');
        }

        $item->decrement('quantity');

        return back()->with('success', 'Cart quantity updated.');
    }

    /**
     * Remove item
     */
    public function remove($id)
    {
        $item = CartItem::with('cart')->findOrFail($id);

        abort_if($item->cart->user_id != auth()->id(), 403);

        $item->delete();

        return back()->with('success', 'Item removed from cart.');
    }

    /**
     * Cart count
     */
    public function count()
    {
        $cart = Cart::with('items')
            ->where('user_id', auth()->id())
            ->where('status', 0)
            ->first();

        $count = $cart
            ? $cart->items->sum('quantity')
            : 0;

        return response()->json([
            'count' => $count,
        ]);
    }
}
