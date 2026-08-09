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
            'statusCountsData'
        ));
    }
}