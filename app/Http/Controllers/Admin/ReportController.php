<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        // Dashboard Statistics
        $totalProducts = Product::count();
        $totalVariants = ProductVariant::count();
        $totalOrders = Order::count();

        $totalCustomers = User::where('role', 'customer')->count();

        $totalInventory = Stock::sum('remaining_quantity');

        $inventoryValue = Stock::selectRaw('SUM(price * remaining_quantity) as total')
            ->value('total');

        $totalSales = Order::where('status', 'completed')
            ->sum('total_amount');

        $averageOrderValue = Order::where('status', 'completed')
            ->avg('total_amount');

        $monthlySales = Order::where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->sum('total_amount');

        $outOfStock = Stock::where('remaining_quantity', 0)
            ->count();

        $lowStock = Stock::where('remaining_quantity', '>', 0)
            ->where('remaining_quantity', '<=', 5)
            ->count();

        // Orders by Status
        $ordersByStatus = Order::select(
                'status',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('status')
            ->get();

        // Sales by Date
        $salesByDate = Order::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as total_sales')
            )
            ->where('status', 'completed')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top Customers
        $topCustomers = Order::select(
                'user_id',
                DB::raw('SUM(total_amount) as total_spent')
            )
            ->where('status', 'completed')
            ->groupBy('user_id')
            ->with('user')
            ->orderByDesc('total_spent')
            ->take(5)
            ->get();

        // Best Selling Products
        $bestSellingProducts = OrderItem::select(
                'product_variant_id',
                DB::raw('SUM(quantity) as total_sold')
            )
            ->groupBy('product_variant_id')
            ->orderByDesc('total_sold')
            ->get();

        return view('admin.reports.index', compact(
            'totalProducts',
            'totalVariants',
            'totalCustomers',
            'totalInventory',
            'inventoryValue',

            'totalSales',
            'totalOrders',
            'averageOrderValue',
            'monthlySales',

            'outOfStock',
            'lowStock',

            'ordersByStatus',
            'salesByDate',
            'topCustomers',
            'bestSellingProducts'
        ));
    }
}