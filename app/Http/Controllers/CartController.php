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
    $cart = Cart::with('items.variant.product')
        ->where('user_id', auth()->id())
        ->where('status', 0)
        ->first();

    return view('cart.index', compact('cart'));
}


    public function store(Request $request)
{
    $request->validate([
        'product_variant_id' => 'required|exists:product_variants,product_variant_id',
        'quantity' => 'required|integer|min:1',
    ]);

    // Get or create cart
    $cart = Cart::firstOrCreate([
        'user_id' => auth()->id(),
        'status' => 0
    ]);

    // Get variant + stock
    $variant = ProductVariant::findOrFail($request->product_variant_id);
    $stock = $variant->stocks()->latest()->first();

    if (!$stock) {
        return back()->with('error', 'No stock available');
    }

    // Find existing item
    $item = CartItem::where('cart_id', $cart->cart_id)
        ->where('product_variant_id', $variant->product_variant_id)
        ->first();

    if ($item) {
        // Update quantity
        $item->update([
            'quantity' => $item->quantity + $request->quantity
        ]);
    } else {
        // Create new item
        CartItem::create([
            'cart_id' => $cart->cart_id,
            'product_variant_id' => $variant->product_variant_id,
            'quantity' => $request->quantity,
            'price' => $stock->price,
        ]);
    }

    return redirect()->route('cart.index')
    ->with('success', 'Added to cart!');
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
$stock = $variant->stocks()->latest()->first();

if ($item->quantity + 1 > $stock->quantity) {
    return response()->json([
        'success' => false,
        'message' => 'Not enough stock'
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
