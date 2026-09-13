<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\ActivityTrackingService;
use App\Services\CartSummary;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function __construct(
        protected StockService $stockService,
        protected ActivityTrackingService $activityTracker,
        protected CartSummary $cartSummary,
    ) {}

    public function index()
    {
        $cart = Cart::query()
            ->with([
                'items' => fn ($items) => $items
                    ->select('cart_item_id', 'cart_id', 'product_variant_id', 'quantity', 'price')
                    ->orderBy('cart_item_id'),
                'items.variant' => fn ($variants) => $variants
                    ->select('product_variant_id', 'product_id', 'size', 'color', 'is_active')
                    ->withStorefrontStock(),
                'items.variant.product' => fn ($products) => $products
                    ->select('product_id', 'product_name')
                    ->with(['images' => fn ($images) => $images
                        ->select('image_id', 'product_id', 'image_path', 'thumbnail_path', 'medium_path', 'large_path', 'image_width', 'image_height', 'thumbnail_width', 'thumbnail_height', 'medium_width', 'medium_height', 'large_width', 'large_height', 'is_primary', 'display_order')
                        ->reorder()->orderByDesc('is_primary')->orderBy('display_order')->orderBy('image_id')->limit(1)]),
            ])
            ->where('user_id', auth()->id())
            ->where('status', 0)
            ->first();

        return view('cart.index', compact('cart'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_variant_id' => ['required', 'integer', 'exists:product_variants,product_variant_id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);

        $variant = ProductVariant::with('product')
            ->where('is_active', true)
            ->whereHas('product', fn ($query) => $query->where('is_active', true))
            ->findOrFail($validated['product_variant_id']);

        $result = DB::transaction(function () use ($validated, $variant) {
            User::whereKey(auth()->id())->lockForUpdate()->firstOrFail();

            $cart = Cart::where('user_id', auth()->id())
                ->where('status', 0)
                ->orderBy('cart_id')
                ->lockForUpdate()
                ->first();

            if (! $cart) {
                $now = now();
                DB::table('carts')->insertOrIgnore([
                    'user_id' => auth()->id(),
                    'status' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $cart = Cart::where('user_id', auth()->id())
                    ->where('status', 0)
                    ->orderBy('cart_id')
                    ->lockForUpdate()
                    ->firstOrFail();
            }

            $item = CartItem::where('cart_id', $cart->cart_id)
                ->where('product_variant_id', $variant->product_variant_id)
                ->lockForUpdate()
                ->first();

            $availableStock = $this->stockService->availableQuantity($variant);
            $newQuantity = (int) ($item?->quantity ?? 0) + $validated['quantity'];

            if ($availableStock <= 0) {
                return ['error' => 'This item is out of stock.'];
            }

            if ($newQuantity > $availableStock) {
                return ['error' => "Only {$availableStock} item(s) available."];
            }

            if ($item) {
                $item->update(['quantity' => $newQuantity]);
            } else {
                $now = now();
                $inserted = DB::table('cart_items')->insertOrIgnore([
                    'cart_id' => $cart->cart_id,
                    'product_variant_id' => $variant->product_variant_id,
                    'quantity' => $validated['quantity'],
                    'price' => $this->stockService->currentPrice($variant),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                if ($inserted === 0) {
                    $item = CartItem::where('cart_id', $cart->cart_id)
                        ->where('product_variant_id', $variant->product_variant_id)
                        ->lockForUpdate()
                        ->firstOrFail();
                    $newQuantity = $item->quantity + $validated['quantity'];
                    if ($newQuantity > $availableStock) {
                        return ['error' => "Only {$availableStock} item(s) available."];
                    }
                    $item->update(['quantity' => $newQuantity]);
                }
            }

            return ['error' => null];
        }, 3);

        if ($result['error']) {
            return back()->with('error', $result['error']);
        }

        $this->activityTracker->logAddToCart(auth()->user(), $variant->product);

        return redirect()->route('cart.index')->with('success', 'Product added to cart.');
    }

    public function increase(Request $request, $id): JsonResponse|RedirectResponse
    {
        $item = $this->ownedItem($id);

        $result = DB::transaction(function () use ($item) {
            User::whereKey(auth()->id())->lockForUpdate()->firstOrFail();
            $locked = CartItem::query()
                ->whereKey($item->getKey())
                ->whereHas('cart', fn ($cart) => $cart
                    ->where('user_id', auth()->id())->where('status', 0))
                ->lockForUpdate()
                ->firstOrFail();

            $variant = ProductVariant::findOrFail($locked->product_variant_id);
            $availableStock = $this->stockService->availableQuantity($variant);

            if ($locked->quantity >= $availableStock) {
                return ['item' => $locked, 'error' => 'Maximum stock reached.'];
            }

            $locked->increment('quantity');
            $locked->refresh();

            return ['item' => $locked, 'error' => null];
        }, 3);

        if ($result['error']) {
            return back()->with('error', $result['error']);
        }

        return $this->quantityUpdateResponse($request, $result['item'], 'Cart quantity updated.');
    }

    public function decrease(Request $request, $id): JsonResponse|RedirectResponse
    {
        $item = $this->ownedItem($id);

        $item = DB::transaction(function () use ($item) {
            User::whereKey(auth()->id())->lockForUpdate()->firstOrFail();
            $locked = CartItem::query()
                ->whereKey($item->getKey())
                ->whereHas('cart', fn ($cart) => $cart
                    ->where('user_id', auth()->id())->where('status', 0))
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->quantity <= 1) {
                $locked->delete();

                return $locked;
            }

            $locked->decrement('quantity');

            return $locked->refresh();
        }, 3);

        return $this->quantityUpdateResponse(
            $request,
            $item,
            $item->exists ? 'Cart quantity updated.' : 'Item removed from cart.',
        );
    }

    public function remove($id): RedirectResponse
    {
        $item = $this->ownedItem($id);

        DB::transaction(function () use ($item) {
            User::whereKey(auth()->id())->lockForUpdate()->firstOrFail();
            CartItem::query()
                ->whereKey($item->getKey())
                ->whereHas('cart', fn ($cart) => $cart
                    ->where('user_id', auth()->id())->where('status', 0))
                ->lockForUpdate()
                ->firstOrFail()
                ->delete();
        }, 3);

        return back()->with('success', 'Item removed from cart.');
    }

    public function count(): JsonResponse
    {
        return response()->json([
            'count' => $this->cartSummary->quantityForUser(auth()->id()),
        ]);
    }

    private function ownedItem(int|string $id): CartItem
    {
        $item = CartItem::with('cart:cart_id,user_id,status')->findOrFail($id);
        abort_if($item->cart->user_id !== auth()->id() || $item->cart->status !== 0, 403);

        return $item;
    }

    private function quantityUpdateResponse(
        Request $request,
        CartItem $item,
        string $message,
    ): JsonResponse|RedirectResponse {
        if (! $request->expectsJson()) {
            return back()->with('success', $message);
        }

        $summary = $this->cartSummary->forCart((int) $item->cart_id);
        $quantity = $item->exists ? (int) $item->quantity : 0;

        return response()->json([
            'quantity' => $quantity,
            'item_subtotal' => $item->exists ? (float) $item->price * $quantity : 0,
            'cart_total' => $summary['total'],
            'cart_count' => $summary['quantity'],
            'message' => $message,
        ]);
    }
}
