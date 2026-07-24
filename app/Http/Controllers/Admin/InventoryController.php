<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index()
{
    // Total remaining stock per variant
    $stockTotals = DB::table('stocks')
        ->select(
            'product_variant_id',
            DB::raw('SUM(remaining_quantity) as total_stock')
        )
        ->groupBy('product_variant_id');

    // Total sold per variant
    $salesTotals = DB::table('order_items')
        ->select(
            'product_variant_id',
            DB::raw('SUM(quantity) as total_sold')
        )
        ->groupBy('product_variant_id');

    $inventory = DB::table('product_variants')
        ->join(
            'products',
            'products.product_id',
            '=',
            'product_variants.product_id'
        )

        ->leftJoinSub($stockTotals, 'stock_totals', function ($join) {
            $join->on(
                'stock_totals.product_variant_id',
                '=',
                'product_variants.product_variant_id'
            );
        })

        ->leftJoinSub($salesTotals, 'sales_totals', function ($join) {
            $join->on(
                'sales_totals.product_variant_id',
                '=',
                'product_variants.product_variant_id'
            );
        })

        ->select(
            'product_variants.product_variant_id',
            'products.product_name',
            'product_variants.size',
            'product_variants.color',

            DB::raw('COALESCE(stock_totals.total_stock,0) as total_stock'),

            DB::raw('COALESCE(sales_totals.total_sold,0) as total_sold'),

            DB::raw('COALESCE(stock_totals.total_stock,0) as available_stock')
        )

        ->orderBy('products.product_name')
        ->get();

    $totalProducts = $inventory->count();

    $lowStock = $inventory
        ->where('available_stock', '>', 0)
        ->where('available_stock', '<=', 5)
        ->count();

    $outOfStock = $inventory
        ->where('available_stock', '<=', 0)
        ->count();

    return view(
        'admin.inventory.index',
        compact(
            'inventory',
            'totalProducts',
            'lowStock',
            'outOfStock'
        )
    );
}
}