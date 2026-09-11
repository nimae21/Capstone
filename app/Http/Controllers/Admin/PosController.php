<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SaleType;
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
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1', 'max:1000'],
        ]);
        $search = trim($validated['search'] ?? '');
        $pattern = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_strtolower($search));

        $products = Product::with([
            'variants' => fn ($query) => $query->where('is_active', true)
                ->with(['stocks' => fn ($stocks) => $stocks->where('is_archived', false)]),
            'brand',
            'category',
        ])
            ->where('is_active', true)
            ->when($search, function ($query) use ($pattern) {
                $query->whereRaw("LOWER(product_name) LIKE ? ESCAPE '!'", ['%'.$pattern.'%']);
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
            'products' => $products,
            'search' => $search,
            'categories' => Category::where('is_active', true)->get(),
            'brands' => Brand::where('is_active', true)->get(),
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
        abort_unless($variant->is_active && $variant->product?->is_active, 404);

        return response()->json([
            'variant_id' => $variant->product_variant_id,
            'product_name' => $variant->product->product_name,
            'size' => $variant->size,
            'color' => $variant->color,
            'available_stock' => $this->stockService->availableQuantity($variant),
            'price' => $this->stockService->currentPrice($variant),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'request_id' => ['required', 'uuid'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.product_variant_id' => ['required', 'integer', 'distinct', 'exists:product_variants,product_variant_id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:1000'],
            'items.*.price' => ['prohibited'],
        ]);

        try {
            $order = $this->orderService->createPosSale($validated['items'], $request->user(), $validated['request_id']);

            return response()->json([
                'success' => true,
                'order_id' => $order->order_id,
                'receipt_url' => route('admin.pos.receipt', $order->order_id),
            ]);

        } catch (InsufficientStockException|\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'error' => 'This POS request was already used for a different sale.'], 409);
        }
    }

    public function receipt(Order $order)
    {
        abort_unless($order->sale_type === SaleType::Pos, 404);
        $order->load('items.variant.product', 'user');

        return view('admin.pos.receipt', compact('order'));
    }
}
