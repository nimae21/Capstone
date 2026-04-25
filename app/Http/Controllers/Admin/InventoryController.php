<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index()
    {
        // 🔥 MAIN INVENTORY QUERY
        $inventory = DB::table('product_variants')
            ->join('products', 'products.product_id', '=', 'product_variants.product_id')

            // total stock IN
            ->leftJoin('stocks', 'stocks.product_variant_id', '=', 'product_variants.product_variant_id')

            // total sold OUT
            ->leftJoin('order_items', 'order_items.product_variant_id', '=', 'product_variants.product_variant_id')

            ->select(
                'product_variants.product_variant_id',
                'products.product_name',
                'product_variants.size',
                'product_variants.color',

                // total stock added
                DB::raw('COALESCE(SUM(stocks.quantity), 0) as total_stock'),

                // total sold
                DB::raw('COALESCE(SUM(order_items.quantity), 0) as total_sold'),

                // available stock
                DB::raw('(COALESCE(SUM(stocks.quantity), 0) - COALESCE(SUM(order_items.quantity), 0)) as available_stock')
            )
            ->groupBy(
                'product_variants.product_variant_id',
                'products.product_name',
                'product_variants.size',
                'product_variants.color'
            )
            ->get();

        // 📊 Summary cards
        $totalProducts = $inventory->count();
        $lowStock = $inventory->where('available_stock', '>', 0)
                              ->where('available_stock', '<=', 5)
                              ->count();

        $outOfStock = $inventory->where('available_stock', '<=', 0)->count();

        return view('admin.inventory.index', compact(
            'inventory',
            'totalProducts',
            'lowStock',
            'outOfStock'
        ));
    }
}