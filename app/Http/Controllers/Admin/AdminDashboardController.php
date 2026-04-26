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

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Total Products
        $totalProducts = Product::count();
        $newProducts = Product::whereMonth('created_at', Carbon::now()->month)->count();

        // Total Orders
        $totalOrders = Order::count();
        $lastMonthOrders = Order::whereMonth('created_at', Carbon::now()->subMonth()->month)->count();
        $ordersGrowth = $lastMonthOrders > 0 ? (($totalOrders - $lastMonthOrders) / $lastMonthOrders) * 100 : 0;

        // Total Users
        $totalUsers = User::count();
        $newUsers = User::whereMonth('created_at', Carbon::now()->month)->count();

        // Low Stock Items (variants with available stock <= 5)
        $lowStockItems = Stock::select('stocks.*', 'products.product_name', 'product_variants.size', 'product_variants.color')
            ->join('product_variants', 'product_variants.product_variant_id', '=', 'stocks.product_variant_id')
            ->join('products', 'products.product_id', '=', 'product_variants.product_id')
            ->where('stocks.quantity', '<=', 5)
            ->count();

        // Low Stock Products for alerts
        $lowStockProducts = Stock::select(
                'stocks.*',
                'products.product_name',
                'product_variants.size as variant_size',
                'product_variants.color as variant_color',
                DB::raw('stocks.quantity as available_stock')
            )
            ->join('product_variants', 'product_variants.product_variant_id', '=', 'stocks.product_variant_id')
            ->join('products', 'products.product_id', '=', 'product_variants.product_id')
            ->where('stocks.quantity', '<=', 5)
            ->orderBy('stocks.quantity', 'asc')
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

        return view('admin.dashboard', compact(
            'totalProducts',
            'newProducts',
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
            'statusCountsData',
            'statusCounts'
        ));
    }
}