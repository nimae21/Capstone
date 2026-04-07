<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductVariant;
use App\Models\Stock;
    

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

    Stock::create([
        'product_variant_id' => $product_variant_id,
        'quantity' => $request->quantity,
        'price' => $request->price,
        'deliver_date' => $request->deliver_date,
    ]);

    return redirect()->route('admin.stocks.index', $product_variant_id)
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
    // Validate input
    $request->validate([
        'quantity' => 'required|integer|min:0',
        'price' => 'required|numeric|min:0',
        'deliver_date' => 'required|date',
    ]);

    // Find the stock
    $stock = Stock::findOrFail($stock_id);

    // Update fields
    $stock->update([
        'quantity' => $request->quantity,
        'price' => $request->price,
        'deliver_date' => $request->deliver_date,
    ]);

    // Redirect back to the stock index page for this variant
    return redirect()->route('admin.stocks.index', $stock->product_variant_id)
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
