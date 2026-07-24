<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\User;
use App\Models\Stock;
use App\Models\ProductVariant;
use App\Models\OrderItem;

class ReportController extends Controller
{


public function index()
{

    $totalProducts = Product::count();

    $totalCustomers = User::where('role', 'customer')->count();

    $totalInventory = Stock::sum('remaining_quantity');

    $inventoryValue = Stock::selectRaw('SUM(price * remaining_quantity) as total')
    ->value('total');

    $totalSales = Order::where('status', 'completed')->sum('total_amount');

    $totalOrders = Order::count();

    $ordersByStatus = Order::select('status', DB::raw('count(*) as total'))
        ->groupBy('status')
        ->get();

    $salesByDate = Order::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(total_amount) as total_sales')
        )
        ->where('status', 'completed')
        ->groupBy('date')
        ->orderBy('date')
        ->get();

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


    $totalVariants = ProductVariant::count();

    $outOfStock = Stock::where('remaining_quantity', 0)->count();

    $lowStock = Stock::where('remaining_quantity', '<=', 5)
                 ->where('remaining_quantity', '>', 0)
                 ->count();

    $averageOrderValue = Order::where('status', 'completed')
                          ->avg('total_amount');

    $monthlySales = Order::where('status', 'completed')
                     ->whereMonth('created_at', now()->month)
                     ->sum('total_amount');

    $bestSellingProducts = OrderItem::select(
    'product_variant_id',
    DB::raw('SUM(quantity) as total_sold')

)
->groupBy('product_variant_id')
->orderByDesc('total_sold')
->get();

    return view('admin.reports.index', compact(
    'totalSales',
    'totalOrders',
    'ordersByStatus',
    'salesByDate',
    'topCustomers',
    'averageOrderValue',
    'monthlySales',
    'bestSellingProducts',
    'totalInventory',
    'lowStock',
    'outOfStock',
    'totalVariants',
    'inventoryValue',
    'totalCustomers',
    'totalProducts'
));
}
}
