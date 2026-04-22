<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\ProductVariant;
use App\Models\CartItem;

class CartController extends Controller
{
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


    public function store(Request $request)
{
    $request->validate([
        'product_variant_id' => 'required',
        'quantity' => 'required|integer|min:1'
    ]);

    $userId = auth()->id();

    // Get active cart
    $cart = Cart::firstOrCreate(
        ['user_id' => $userId, 'status' => 0]
    );

    // Get variant
    $variant = ProductVariant::with('stocks')
        ->where('product_variant_id', $request->product_variant_id)
        ->firstOrFail();

    // Get stock (latest or first)
    $stock = $variant->stocks->last();

    if (!$stock || $stock->quantity <= 0) {
        return back()->with('error', 'Out of stock');
    }

    // Check if item already exists in cart
    $existingItem = CartItem::where('cart_id', $cart->cart_id)
    ->where('product_variant_id', $request->product_variant_id)
    ->first();

    if ($existingItem) {
        // Update quantity instead of duplicate
        $newQty = $existingItem->quantity + $request->quantity;

        if ($newQty > $stock->quantity) {
            return back()->with('error', 'Not enough stock');
        }

        $existingItem->update([
            'quantity' => $newQty
        ]);
    } else {
        // Create new item
        if ($request->quantity > $stock->quantity) {
            return back()->with('error', 'Not enough stock');
        }

        CartItem::create([
    'cart_id' => $cart->cart_id,
    'product_variant_id' => $request->product_variant_id,
    'quantity' => $request->quantity,
    'price' => $stock->price // ✅ THIS IS THE FIX
]);     
    }

    return redirect()->route('cart.index')->with('success', 'Added to cart');
}

public function increase($id)
{
    $item = CartItem::with('cart')->findOrFail($id);

    abort_if(!$item->cart || $item->cart->user_id !== auth()->id(), 403);

    $item->increment('quantity');

    return response()->json([
        'success' => true,
        'quantity' => $item->quantity
    ]);
    $variant = $item->variant;
$stock = $cartItem->variant->stocks->last();

if ($cartItem->quantity >= $stock->quantity) {
    return response()->json([
        'error' => 'Max stock reached'
    ], 400);

}

}

public function decrease($id)
{
    $item = CartItem::with('cart')->findOrFail($id);

    abort_if(!$item->cart || $item->cart->user_id !== auth()->id(), 403);

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

public function remove($id)
{
    $item = CartItem::with('cart')->findOrFail($id);

    abort_if(!$item->cart || $item->cart->user_id !== auth()->id(), 403);

    $item->delete();

    return response()->json([
        'success' => true
    ]);
}
}
