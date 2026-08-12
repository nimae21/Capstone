<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\OrderService;
use App\Services\StockService;
use Illuminate\Http\Request;

class PosController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected StockService $stockService
    ) {}

    public function index(Request $request)
    {
        $search = trim((string) $request->search);

        $products = Product::with(['variants.stocks', 'brand', 'category'])
            ->where('is_active', true)
            ->when($search, function ($query) use ($search) {
                $query->whereRaw('LOWER(product_name) LIKE ?', ['%' . strtolower($search) . '%']);
            })
            ->orderBy('product_name')
            ->paginate(12)
            ->withQueryString();

        // Attach live stock/price to each variant for the picker UI
        foreach ($products as $product) {
            foreach ($product->variants as $variant) {
                $variant->available_stock = $this->stockService->availableQuantity($variant);
                $variant->current_price = $this->stockService->currentPrice($variant);
            }
        }

        return view('admin.pos.index', [
            'products'   => $products,
            'search'     => $search,
            'categories' => Category::where('is_active', true)->get(),
            'brands'     => Brand::where('is_active', true)->get(),
        ]);
    }

    /**
     * AJAX endpoint: look up a variant's live price/stock
     * (not currently called by the UI, but available if you want
     * to re-verify stock right before checkout instead of relying
     * solely on the page-load snapshot).
     */
    public function variantInfo(ProductVariant $variant)
    {
        return response()->json([
            'variant_id'      => $variant->product_variant_id,
            'product_name'    => $variant->product->product_name,
            'size'            => $variant->size,
            'color'           => $variant->color,
            'available_stock' => $this->stockService->availableQuantity($variant),
            'price'           => $this->stockService->currentPrice($variant),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'items'                       => ['required', 'array', 'min:1'],
            'items.*.product_variant_id'  => ['required', 'exists:product_variants,product_variant_id'],
            'items.*.quantity'            => ['required', 'integer', 'min:1'],
            'items.*.price'               => ['required', 'numeric', 'min:0'],
        ]);

        try {
            $order = $this->orderService->createPosSale($validated['items'], $request->user());

            return response()->json([
                'success'     => true,
                'order_id'    => $order->order_id,
                'receipt_url' => route('admin.pos.receipt', $order->order_id),
            ]);

        } catch (InsufficientStockException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }

    public function receipt(Order $order)
    {
        $order->load('items.variant.product', 'user');

        return view('admin.pos.receipt', compact('order'));
    }
}