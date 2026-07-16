<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductVariant;
use App\Models\Stock;
use App\Models\StockMovement;
    

class StockController extends Controller
{
    public function index($product_variant_id)
{
    $variant = ProductVariant::findOrFail($product_variant_id);
    $stocks = $variant->stocks()
    ->where('is_archived', false)
    ->get(); // all stock entries for this variant

    return view('admin.stocks.index', compact('variant', 'stocks'));
}
    public function store(Request $request, $product_variant_id)
{
    $request->validate([
        'received_quantity' => 'required|integer|min:1',
        'price' => 'required|numeric|min:0.01',
        'deliver_date' => 'required|date',
    ]);

    $stock = Stock::create([
    'product_variant_id' => $product_variant_id,
    'received_quantity' => $request->received_quantity,
    'remaining_quantity' => $request->received_quantity,
    'price' => $request->price,
    'deliver_date' => $request->deliver_date,
]);

    // Record stock movement
    StockMovement::create([
        'stock_id' => $stock->stock_id,
        'quantity' => $request->received_quantity,
        'type' => 'in',
    ]);

    return redirect()
        ->route('admin.stocks.index', $product_variant_id)
        ->with('success', 'Stock added successfully.');
}
    public function edit($stock_id)
    {
        $stock = Stock::findOrFail($stock_id);
        $variant = $stock->variant; // get the product variant

        return view('admin.stocks.edit', compact('stock', 'variant'));
    }
    public function update(Request $request, $stock_id)
{
    $request->validate([
        'price' => 'required|numeric|min:0.01',
        'deliver_date' => 'required|date',
    ]);

    $stock = Stock::findOrFail($stock_id);

    $stock->update([
        'price' => $request->price,
        'deliver_date' => $request->deliver_date,
    ]);

    return redirect()
        ->route('admin.stocks.index', $stock->product_variant_id)
        ->with('success', 'Stock updated successfully.');
}
    public function destroy($stock_id)
{
    $stock = Stock::findOrFail($stock_id);

    if ($stock->remaining_quantity > 0) {

        return back()->with(
            'error',
            'You cannot archive a stock batch that still has remaining inventory.'
        );

    }

    $stock->update([
        'is_archived' => true
    ]);

    return back()->with(
        'success',
        'Stock batch archived successfully.'
    );
}
    }
