<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Services\ActivityTrackingService;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
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
        $validated = $request->validate([
            'product_variant_id' => ['required', 'integer', 'exists:product_variants,product_variant_id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);

        $variant = ProductVariant::with('product')
            ->where('is_active', true)
            ->whereHas('product', fn ($query) => $query->where('is_active', true))
            ->findOrFail($validated['product_variant_id']);

        $availableStock = $this->stockService->availableQuantity($variant);

        if ($availableStock <= 0) {
            return back()->with('error', 'This item is out of stock.');
        }

        if ($validated['quantity'] > $availableStock) {
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

            $newQuantity = $item->quantity + $validated['quantity'];

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
                'quantity' => $validated['quantity'],
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
    public function increase(Request $request, $id): JsonResponse|RedirectResponse
    {
        $item = CartItem::with('cart', 'variant')->findOrFail($id);

        abort_if($item->cart->user_id != auth()->id(), 403);

        $availableStock = $this->stockService->availableQuantity($item->variant);

        if ($item->quantity >= $availableStock) {
            return back()->with('error', 'Maximum stock reached.');
        }

        $item->increment('quantity');

        return $this->quantityUpdateResponse($request, $item, 'Cart quantity updated.');
    }

    /**
     * Decrease quantity
     */
    public function decrease(Request $request, $id): JsonResponse|RedirectResponse
    {
        $item = CartItem::with('cart')->findOrFail($id);

        abort_if($item->cart->user_id != auth()->id(), 403);

        if ($item->quantity <= 1) {

            $item->delete();

            return $this->quantityUpdateResponse($request, $item, 'Item removed from cart.');
        }

        $item->decrement('quantity');

        return $this->quantityUpdateResponse($request, $item, 'Cart quantity updated.');
    }

    private function quantityUpdateResponse(Request $request, CartItem $item, string $message): JsonResponse|RedirectResponse
    {
        if (! $request->expectsJson()) {
            return back()->with('success', $message);
        }

        $cart = $item->cart()->with('items')->first();
        $quantity = $item->exists ? $item->quantity : 0;
        $itemSubtotal = $item->exists ? $item->price * $quantity : 0;

        return response()->json([
            'quantity' => $quantity,
            'item_subtotal' => $itemSubtotal,
            'cart_total' => $cart?->items->sum(fn (CartItem $cartItem): float|int => $cartItem->price * $cartItem->quantity) ?? 0,
            'cart_count' => $cart?->items->sum('quantity') ?? 0,
            'message' => $message,
        ]);
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
