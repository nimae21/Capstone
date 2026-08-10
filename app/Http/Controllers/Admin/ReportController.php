<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Stock;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $data = $this->gatherReportData($request);

        return view('admin.reports.index', $data);
    }

    public function exportPdf(Request $request)
    {
        $data = $this->gatherReportData($request);

        $pdf = Pdf::loadView('admin.reports.pdf', $data)
            ->setPaper('a4', 'portrait');

        $filename = 'sales-report-' . ($data['selectedYear'] ?? now()->year) . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Single source of truth for report data — used by both the
     * on-screen report and the PDF export, so the two never drift apart.
     */
    private function gatherReportData(Request $request): array
    {
        $totalProducts = Product::count();
        $totalVariants = ProductVariant::count();
        $totalOrders = Order::count();
        $totalCustomers = User::where('role', 'user')->count();
        $totalInventory = Stock::sum('remaining_quantity');
        $inventoryValue = Stock::selectRaw('SUM(price * remaining_quantity) as total')->value('total');

        $totalSales = Order::where('status', 'completed')->sum('total_amount');
        $averageOrderValue = Order::where('status', 'completed')->avg('total_amount');
        $monthlySales = Order::where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->sum('total_amount');

        $outOfStock = Stock::where('remaining_quantity', 0)->count();
        $lowStock = Stock::where('remaining_quantity', '>', 0)
            ->where('remaining_quantity', '<=', 5)
            ->count();

        $ordersByStatus = Order::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->get();

        $completedCount = $ordersByStatus->first(
            fn ($row) => $row->status === OrderStatus::Completed
        )?->total ?? 0;

        $salesByDate = Order::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as total_sales')
            )
            ->where('status', 'completed')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $topCustomers = Order::select('user_id', DB::raw('SUM(total_amount) as total_spent'))
            ->where('status', 'completed')
            ->groupBy('user_id')
            ->with('user')
            ->orderByDesc('total_spent')
            ->take(5)
            ->get();

        $bestSellingProducts = OrderItem::select(
                'product_variant_id',
                DB::raw('SUM(quantity) as total_sold')
            )
            ->groupBy('product_variant_id')
            ->orderByDesc('total_sold')
            ->get();

        $selectedYear = $request->input('year', now()->year);

       $monthlyTrend = Order::selectRaw('EXTRACT(MONTH FROM created_at) as month, SUM(total_amount) as total_sales')
    ->where('status', 'completed')
    ->whereYear('created_at', $selectedYear)
    ->groupBy(DB::raw('EXTRACT(MONTH FROM created_at)'))
    ->orderBy('month')
    ->get()
    ->keyBy('month');

        $monthlyLabels = [];
        $monthlySalesData = [];

        for ($m = 1; $m <= 12; $m++) {
            $monthlyLabels[] = \Carbon\Carbon::create()->month($m)->format('M');
            $monthlySalesData[] = $monthlyTrend->get($m)?->total_sales ?? 0;
        }

        $yearlyTrend = Order::selectRaw('EXTRACT(YEAR FROM created_at) as year, SUM(total_amount) as total_sales')
    ->where('status', 'completed')
    ->groupBy(DB::raw('EXTRACT(YEAR FROM created_at)'))
    ->orderBy('year')
    ->get();

$availableYears = Order::selectRaw('DISTINCT EXTRACT(YEAR FROM created_at) as year')
    ->orderByDesc('year')
    ->pluck('year');

        $salesByProvince = Order::select(
                'province',
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total_amount) as total_sales')
            )
            ->where('status', 'completed')
            ->groupBy('province')
            ->orderByDesc('order_count')
            ->get();

        $salesByCity = Order::select(
                'city', 'province',
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total_amount) as total_sales')
            )
            ->where('status', 'completed')
            ->groupBy('city', 'province')
            ->orderByDesc('order_count')
            ->take(10)
            ->get();

        return compact(
            'totalProducts', 'totalVariants', 'totalCustomers', 'totalInventory', 'inventoryValue',
            'totalSales', 'totalOrders', 'averageOrderValue', 'monthlySales',
            'outOfStock', 'lowStock',
            'ordersByStatus', 'salesByDate', 'topCustomers', 'bestSellingProducts', 'completedCount',
            'selectedYear', 'monthlyLabels', 'monthlySalesData', 'yearlyTrend', 'availableYears',
            'salesByProvince', 'salesByCity'
        );
    }
}