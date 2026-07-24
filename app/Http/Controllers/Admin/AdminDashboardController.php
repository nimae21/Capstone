<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Order;
use App\Models\User;
use App\Models\ProductVariant;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\StockMovement;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $stockTotals = Stock::select(
    'product_variant_id',
    DB::raw('SUM(remaining_quantity) as total_remaining')
)
->groupBy('product_variant_id');
        // Total Products
        $totalProducts = Product::count();
        $newProducts = Product::whereMonth('created_at', Carbon::now()->month)->count();

        //Total Variants
        $totalVariants = ProductVariant::count();

        // Total Inventory
        $totalInventory = Stock::sum('remaining_quantity');

        //inventory Value
       $inventoryValue = Stock::selectRaw('SUM(price * remaining_quantity) as total')
    ->value('total');

        $outOfStock = DB::query()
    ->fromSub($stockTotals, 'stock_totals')
    ->where('total_remaining', '<=', 0)
    ->count();
        // Total Orders
        $totalOrders = Order::count();
        $lastMonthOrders = Order::whereMonth('created_at', Carbon::now()->subMonth()->month)->count();
        $ordersGrowth = $lastMonthOrders > 0 ? (($totalOrders - $lastMonthOrders) / $lastMonthOrders) * 100 : 0;

        // Total Users
        $totalUsers = User::count();
        
        $newUsers = User::whereMonth('created_at', Carbon::now()->month)->count();

        // Low Stock Items (variants with available stock <= 5)
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

        // Low Stock Products for alerts
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
        DB::raw('COALESCE(stock_totals.total_remaining,0) as available_stock')
    )

    ->whereBetween('stock_totals.total_remaining', [1,5])

    ->orderBy('available_stock')

    ->limit(5)

    ->get();

        // Recent Orders
        $recentOrders = Order::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        

        // Chart Data - Last 7 days revenue
        $chartLabels = [];
        $chartData = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $chartLabels[] = $date->format('D, M d');
            
            $dailyRevenue = Order::where('status', 'completed')
                ->whereDate('created_at', $date)
                ->sum('total_amount');
            
            $chartData[] = $dailyRevenue;
        }

        // Order Status Distribution
        $statusCounts = Order::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();
        
        $statusLabels = $statusCounts->pluck('status')->map(function($status) {
            return ucfirst($status);
        });
        $statusCountsData = $statusCounts->pluck('total');

        $recentStockMovements = StockMovement::with([
            'stock.variant.product'
        ])
        ->latest()
        ->take(5)
        ->get();

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

    'chartLabels',
    'chartData',

    'statusLabels',
    'statusCountsData'
));
    }
}