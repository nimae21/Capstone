<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Models\Stock;
use App\Services\ApprovalService;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index(ProductVariant $variant)
    {
        $stocks = $variant->stocks()
            ->where('is_archived', false)
            ->latest()
            ->get();

        $productVariants = ProductVariant::query()
            ->where('product_id', $variant->product_id)
            ->where('is_active', true)
            ->with(['stocks' => fn ($query) => $query->where('is_archived', false)])
            ->orderBy('color')
            ->orderByRaw('CAST(size AS DECIMAL(4,1))')
            ->get();

        return view('admin.stocks.index', compact('variant', 'stocks', 'productVariants'));
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
