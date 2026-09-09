<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Stock;
use App\Models\User;
use App\Services\ProductSales;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index()
    {
        $now = Carbon::now();

        // --- Summary cards ---
        $totalRevenue   = Order::where('status', OrderStatus::Completed)->sum('total_amount');
        $totalOrders    = Order::count();
        $totalCustomers = User::where('role', 'user')->count();
        $pendingOrders  = Order::where('status', OrderStatus::Paid)->count(); // Paid = awaiting shipment

        // --- Order status breakdown ---
        $statusCounts = Order::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->get()
            ->map(fn($row) => [
                'status' => $row->status->label(),
                'total'  => $row->total,
            ]);

        // --- Top 5 best sellers (reuses ProductSales service like your dashboard) ---
        $bestSellers = DB::query()
            ->fromSub(ProductSales::totals(), 'sales')
            ->join('products', 'products.product_id', '=', 'sales.product_id')
            ->select('products.product_name', 'sales.total_sold')
            ->orderByDesc('total_sold')
            ->take(5)
            ->get();

        // --- Monthly revenue for current year ---
        $monthlyRevenue = Order::where('status', OrderStatus::Completed)
            ->selectRaw("EXTRACT(MONTH FROM created_at) as month, SUM(total_amount) as total")
            ->whereYear('created_at', $now->year)
            ->groupByRaw("EXTRACT(MONTH FROM created_at)")
            ->orderByRaw("EXTRACT(MONTH FROM created_at)")
            ->get()
            ->map(fn($row) => [
                'month' => Carbon::create()->month($row->month)->format('M'),
                'total' => $row->total,
            ]);

        // --- Last 7 days revenue ---
        $last7Days = collect(range(6, 0))->map(function ($daysAgo) use ($now) {
            $date = $now->copy()->subDays($daysAgo);
            return [
                'date'  => $date->format('D, M d'),
                'total' => Order::where('status', OrderStatus::Completed)
                    ->whereDate('created_at', $date)
                    ->sum('total_amount'),
            ];
        });

        // --- Low stock count ---
        $stockTotals = Stock::select(
            'product_variant_id',
            DB::raw('SUM(remaining_quantity) as total_remaining')
        )->groupBy('product_variant_id');

        $lowStockCount = DB::query()
            ->fromSub($stockTotals, 'stock_totals')
            ->whereBetween('total_remaining', [1, 5])
            ->count();

        return response()->json([
            'summary' => [
                'total_revenue'   => $totalRevenue,
                'total_orders'    => $totalOrders,
                'total_customers' => $totalCustomers,
                'pending_orders'  => $pendingOrders,
                'low_stock'       => $lowStockCount,
            ],
            'status_counts'   => $statusCounts,
            'best_sellers'    => $bestSellers,
            'monthly_revenue' => $monthlyRevenue,
            'last_7_days'     => $last7Days,
        ]);
    }
}