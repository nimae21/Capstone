<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;

class CartController extends Controller
{
    /**
     * Display cart
     */
    public function index()
    {
        $cart = Cart::with([
            'items.variant.product',
            'items.variant.stocks'
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

        $variant = ProductVariant::with('stocks')
            ->findOrFail($request->product_variant_id);

        $stock = $variant->stocks->last();

        if (!$stock) {
            return back()->with('error', 'Stock record not found.');
        }

        $availableStock = $stock->remaining_quantity;

        if ($availableStock <= 0) {
            return back()->with('error', 'This item is out of stock.');
        }

        if ($request->quantity > $availableStock) {
            return back()->with('error', "Only {$availableStock} item(s) available.");
        }

        $cart = Cart::firstOrCreate([
            'user_id' => auth()->id(),
            'status' => 0
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
                'quantity' => $newQuantity
            ]);

        } else {

            CartItem::create([
                'cart_id' => $cart->cart_id,
                'product_variant_id' => $variant->product_variant_id,
                'quantity' => $request->quantity,
                'price' => $stock->price
            ]);

        }

        return redirect()
            ->route('cart.index')
            ->with('success', 'Product added to cart.');
    }

    /**
     * Increase quantity
     */
    public function increase($id)
    {
        $item = CartItem::with([
            'cart',
            'variant.stocks'
        ])->findOrFail($id);

        abort_if($item->cart->user_id != auth()->id(), 403);

        $stock = $item->variant->stocks->last();

        if (!$stock) {
            return response()->json([
                'error' => 'Stock not found.'
            ], 404);
        }

        if ($item->quantity >= $stock->remaining_quantity) {
            return response()->json([
                'error' => 'Maximum stock reached.'
            ], 400);
        }

        $item->increment('quantity');

        return response()->json([
            'success' => true,
            'quantity' => $item->quantity
        ]);
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

            return response()->json([
                'success' => true,
                'removed' => true
            ]);
        }

        $item->decrement('quantity');

        return response()->json([
            'success' => true,
            'quantity' => $item->quantity
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

        return response()->json([
            'success' => true
        ]);
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
            'count' => $count
        ]);
    }
}