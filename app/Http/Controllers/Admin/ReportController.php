<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{


public function index()
{
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

    return view('admin.reports.index', compact(
        'totalSales',
        'totalOrders',
        'ordersByStatus',
        'salesByDate',
        'topCustomers'
    ));
}
}
