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
    $stocks = $variant->stocks; // all stock entries for this variant

    return view('admin.stocks.index', compact('variant', 'stocks'));
}
    public function store(Request $request, $product_variant_id)
{
    $request->validate([
        'quantity' => 'required|integer|min:0',
        'price' => 'required|numeric|min:0',
        'deliver_date' => 'required|date',
    ]);

    $stock = Stock::create([
        'product_variant_id' => $product_variant_id,
        'quantity' => $request->quantity,
        'price' => $request->price,
        'deliver_date' => $request->deliver_date,
    ]);

    // Record stock movement
    StockMovement::create([
        'stock_id' => $stock->stock_id,
        'quantity' => $request->quantity,
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
        'quantity' => 'required|integer|min:0',
        'price' => 'required|numeric|min:0',
        'deliver_date' => 'required|date',
    ]);

    $stock = Stock::findOrFail($stock_id);

    // Calculate the quantity difference
    $difference = $request->quantity - $stock->quantity;

    // Update stock
    $stock->update([
        'quantity' => $request->quantity,
        'price' => $request->price,
        'deliver_date' => $request->deliver_date,
    ]);

    // Only record movement if quantity changed
    if ($difference != 0) {

        StockMovement::create([
            'stock_id' => $stock->stock_id,
            'quantity' => $difference,
            'type' => 'adjustment',
        ]);

    }

    return redirect()
        ->route('admin.stocks.index', $stock->product_variant_id)
        ->with('success', 'Stock updated successfully.');
}
    // Delete a stock entry
    public function destroy($stock_id)
    {
        $stock = Stock::findOrFail($stock_id);
        $product_variant_id = $stock->product_variant_id;
        $stock->delete();

        return redirect()->route('admin.stocks.index', $product_variant_id)
                         ->with('success', 'Stock deleted successfully!');
    }
    }
