<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Models\Stock;
use App\Services\ApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StockController extends Controller
{
    public function index(ProductVariant $variant): View
    {
        $stocks = $variant->stocks()
            ->select('stock_id', 'product_variant_id', 'remaining_quantity', 'price', 'deliver_date')
            ->where('is_archived', false)
            ->latest()
            ->orderByDesc('stock_id')
            ->paginate(20, ['*'], 'stock_page')
            ->withQueryString();

        $stockSummary = DB::table('stocks')
            ->where('product_variant_id', $variant->product_variant_id)
            ->where('is_archived', false)
            ->selectRaw('COUNT(*) as entry_count, COALESCE(SUM(remaining_quantity), 0) as remaining_quantity')
            ->first();

        $productVariants = ProductVariant::query()
            ->select('product_variant_id', 'product_id', 'size', 'color')
            ->where('product_id', $variant->product_id)
            ->where('is_active', true)
            ->withSum([
                'stocks as available_stock' => fn ($query) => $query->where('is_archived', false),
            ], 'remaining_quantity')
            ->orderByRaw('CASE WHEN product_variant_id = ? THEN 0 ELSE 1 END', [$variant->product_variant_id])
            ->orderBy('color')
            ->orderByRaw('CAST(size AS DECIMAL(4,1))')
            ->paginate(24, ['*'], 'variant_page')
            ->withQueryString();

        return view('admin.stocks.index', compact('variant', 'stocks', 'stockSummary', 'productVariants'));
    }

    public function store(Request $request, ProductVariant $variant)
    {
        return app(ApprovalService::class)->submit($request, 'stock', ['product_variant_id' => $variant->product_variant_id]);
    }

    public function edit(Stock $stock)
    {
        return view('admin.stocks.edit', [
            'stock' => $stock,
            'variant' => $stock->variant,
        ]);
    }

    public function update(Request $request, Stock $stock)
    {
        $validated = $request->validate([
            'price' => 'required|numeric|min:0.01',
            'deliver_date' => 'required|date|before_or_equal:today',
        ]);

        $stock->update($validated);

        return redirect()
            ->route('admin.stocks.index', $stock->variant)
            ->with('success', 'Stock updated successfully.');
    }

    public function destroy(Stock $stock)
    {
        if ($stock->remaining_quantity > 0) {
            return back()->with(
                'error',
                'You cannot archive a stock batch that still has remaining inventory.'
            );
        }

        $stock->update([
            'is_archived' => true,
        ]);

        return back()->with(
            'success',
            'Stock batch archived successfully.'
        );
    }
}
