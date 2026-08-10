<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();

        /*
|--------------------------------------------------------------------------
| Top 5 Best-Selling Products (by quantity sold)
|--------------------------------------------------------------------------
*/
$topProducts = DB::table('order_items')
    ->join('product_variants', 'product_variants.product_variant_id', '=', 'order_items.product_variant_id')
    ->join('products', 'products.product_id', '=', 'product_variants.product_id')
    ->select(
        'products.product_id',
        'products.product_name',
        DB::raw('SUM(order_items.quantity) as total_sold')
    )
    ->groupBy('products.product_id', 'products.product_name')
    ->orderByDesc('total_sold')
    ->take(5)
    ->get();

/*
|--------------------------------------------------------------------------
| Top Shoe Types (by quantity sold)
|--------------------------------------------------------------------------
*/
$topShoeTypes = DB::table('order_items')
    ->join('product_variants', 'product_variants.product_variant_id', '=', 'order_items.product_variant_id')
    ->join('products', 'products.product_id', '=', 'product_variants.product_id')
    ->join('shoe_types', 'shoe_types.shoe_type_id', '=', 'products.shoe_type_id')
    ->select(
        'shoe_types.shoe_type_name',
        DB::raw('SUM(order_items.quantity) as total_sold')
    )
    ->groupBy('shoe_types.shoe_type_id', 'shoe_types.shoe_type_name')
    ->orderByDesc('total_sold')
    ->take(5)
    ->get();

    /*
|--------------------------------------------------------------------------
| Best-Selling Attributes (Size / Brand / Type / Category)
|--------------------------------------------------------------------------
*/
$topSize = DB::table('order_items')
    ->join('product_variants', 'product_variants.product_variant_id', '=', 'order_items.product_variant_id')
    ->select('product_variants.size', DB::raw('SUM(order_items.quantity) as total_sold'))
    ->groupBy('product_variants.size')
    ->orderByDesc('total_sold')
    ->first();

$topBrand = DB::table('order_items')
    ->join('product_variants', 'product_variants.product_variant_id', '=', 'order_items.product_variant_id')
    ->join('products', 'products.product_id', '=', 'product_variants.product_id')
    ->join('brands', 'brands.brand_id', '=', 'products.brand_id')
    ->select('brands.brand_name', DB::raw('SUM(order_items.quantity) as total_sold'))
    ->groupBy('brands.brand_id', 'brands.brand_name')
    ->orderByDesc('total_sold')
    ->first();

$topType = DB::table('order_items')
    ->join('product_variants', 'product_variants.product_variant_id', '=', 'order_items.product_variant_id')
    ->join('products', 'products.product_id', '=', 'product_variants.product_id')
    ->join('shoe_types', 'shoe_types.shoe_type_id', '=', 'products.shoe_type_id')
    ->select('shoe_types.shoe_type_name', DB::raw('SUM(order_items.quantity) as total_sold'))
    ->groupBy('shoe_types.shoe_type_id', 'shoe_types.shoe_type_name')
    ->orderByDesc('total_sold')
    ->first();

$topCategory = DB::table('order_items')
    ->join('product_variants', 'product_variants.product_variant_id', '=', 'order_items.product_variant_id')
    ->join('products', 'products.product_id', '=', 'product_variants.product_id')
    ->join('categories', 'categories.category_id', '=', 'products.category_id')
    ->select('categories.category_name', DB::raw('SUM(order_items.quantity) as total_sold'))
    ->groupBy('categories.category_id', 'categories.category_name')
    ->orderByDesc('total_sold')
    ->first();
        /*
        |--------------------------------------------------------------------------
        | Stock Totals Per Variant
        |--------------------------------------------------------------------------
        */
        $stockTotals = Stock::select(
                'product_variant_id',
                DB::raw('SUM(remaining_quantity) as total_remaining')
            )
            ->groupBy('product_variant_id');

        /*
        |--------------------------------------------------------------------------
        | Dashboard Statistics
        |--------------------------------------------------------------------------
        */
        $totalProducts = Product::count();
        $newProducts = Product::whereMonth('created_at', $now->month)->count();

        $totalVariants = ProductVariant::count();

        $totalInventory = Stock::sum('remaining_quantity');

        $inventoryValue = Stock::selectRaw(
            'SUM(price * remaining_quantity) as total'
        )->value('total');

        $outOfStock = DB::query()
            ->fromSub($stockTotals, 'stock_totals')
            ->where('total_remaining', '<=', 0)
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Orders
        |--------------------------------------------------------------------------
        */
        $totalOrders = Order::count();

        $lastMonthOrders = Order::whereMonth(
            'created_at',
            $now->copy()->subMonth()->month
        )->count();

        $ordersGrowth = $lastMonthOrders > 0
            ? (($totalOrders - $lastMonthOrders) / $lastMonthOrders) * 100
            : 0;

        /*
        |--------------------------------------------------------------------------
        | Users
        |--------------------------------------------------------------------------
        */
        $totalUsers = User::count();

        $newUsers = User::whereMonth('created_at', $now->month)->count();

        /*
        |--------------------------------------------------------------------------
        | Low Stock Summary
        |--------------------------------------------------------------------------
        */
        $lowStockItems = DB::table('product_variants')
            ->leftJoinSub($stockTotals, 'stock_totals', function ($join) {
                $join->on(
                    'stock_totals.product_variant_id',
                    '=',
                    'product_variants.product_variant_id'
                );
            })
            ->whereBetween('stock_totals.total_remaining', [1, 5])
            ->count();

        $lowStockProducts = DB::table('product_variants')
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
            ->select(
                'products.product_name',
                'product_variants.size as variant_size',
                'product_variants.color as variant_color',
                DB::raw('COALESCE(stock_totals.total_remaining, 0) as available_stock')
            )
            ->whereBetween('stock_totals.total_remaining', [1, 5])
            ->orderBy('available_stock')
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Recent Orders
        |--------------------------------------------------------------------------
        */
        $recentOrders = Order::with('user')
            ->latest()
            ->take(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Recent Stock Movements
        |--------------------------------------------------------------------------
        */
        $recentStockMovements = StockMovement::with([
                'stock.variant.product'
            ])
            ->latest()
            ->take(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Revenue Chart (Last 7 Days)
        |--------------------------------------------------------------------------
        */
        $chartLabels = [];
        $chartData = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);

            $chartLabels[] = $date->format('D, M d');

            $chartData[] = Order::where('status', 'completed')
                ->whereDate('created_at', $date)
                ->sum('total_amount');
        }

        /*
        |--------------------------------------------------------------------------
        | Order Status Distribution
        |--------------------------------------------------------------------------
        */
        $statusCounts = Order::select(
                'status',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('status')
            ->get();

        $statusLabels = $statusCounts
    ->pluck('status')
    ->map(fn ($status) => $status->label());

        $statusCountsData = $statusCounts->pluck('total');

        return view('admin.dashboard', compact(
            'totalProducts',
            'newProducts',
            'totalVariants',
            'totalInventory',
            'inventoryValue',
            'outOfStock',
            'totalOrders',
            'ordersGrowth',
            'totalUsers',
            'newUsers',
            'lowStockItems',
            'lowStockProducts',
            'recentOrders',
            'recentStockMovements',
            'chartLabels',
            'chartData',
            'statusLabels',
            'statusCountsData',
            'topProducts',
            'topShoeTypes',
            'topSize',
            'topBrand',
            'topType',
            'topCategory'
        ));
    }
}