<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function index(ProductVariant $variant)
    {
        $stocks = $variant->stocks()
            ->where('is_archived', false)
            ->latest()
            ->get();

        return view('admin.stocks.index', compact('variant', 'stocks'));
    }

    public function store(Request $request, ProductVariant $variant)
    {
        $validated = $request->validate([
            'received_quantity' => 'required|integer|min:1',
            'price'             => 'required|numeric|min:0.01',
            'deliver_date'      => 'required|date',
        ]);

        DB::transaction(function () use ($validated, $variant) {

            $stock = Stock::create([
                'product_variant_id' => $variant->product_variant_id,
                'received_quantity'  => $validated['received_quantity'],
                'remaining_quantity' => $validated['received_quantity'],
                'price'              => $validated['price'],
                'deliver_date'       => $validated['deliver_date'],
            ]);

            StockMovement::create([
                'stock_id' => $stock->stock_id,
                'quantity' => $validated['received_quantity'],
                'type'     => 'in',
            ]);
        });

        return redirect()
            ->route('admin.stocks.index', $variant)
            ->with('success', 'Stock added successfully.');
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
            'price'        => 'required|numeric|min:0.01',
            'deliver_date' => 'required|date',
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