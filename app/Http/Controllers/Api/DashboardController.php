<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AdminInvitation;
use App\Models\ApprovalRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Stock;
use App\Models\User;
use App\Support\OrderPresenter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Mobile-first overview of the whole Achilles system. Every number is derived
 * from the same tables the website dashboard reads - nothing mobile-specific.
 */
class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today();
        $stockTotals = Stock::select('product_variant_id', DB::raw('SUM(remaining_quantity) as total_remaining'))
            ->where('is_archived', false)
            ->groupBy('product_variant_id');

        $summary = [
            'products' => Product::count(),
            'variants' => ProductVariant::count(),
            'inventory_quantity' => (int) Stock::where('is_archived', false)->sum('remaining_quantity'),
            'inventory_value' => round((float) Stock::where('is_archived', false)->selectRaw('COALESCE(SUM(price * remaining_quantity), 0) as total')->value('total'), 2),
            'out_of_stock' => $this->variantCount($stockTotals, '<=', 0),
            'low_stock' => $this->variantCount($stockTotals, 'between', [1, 5]),
        ];

        $summary = array_merge($summary, [
            'users_total' => User::where('role', 'user')->count(),
            'users_active' => User::where('role', 'user')->where('is_active', true)->count(),
            'users_suspended' => User::where('role', 'user')->where('is_active', false)->count(),
            'admins_total' => User::where('role', 'admin')->count(),
            'admins_active' => User::where('role', 'admin')->where('is_active', true)->count(),
            'admins_suspended' => User::where('role', 'admin')->where('is_active', false)->count(),
            'invitations_pending' => AdminInvitation::whereNull('accepted_at')->where('expires_at', '>', now())->count(),
            'approvals_pending' => ApprovalRequest::where('status', 'pending')->count(),
            'orders_total' => Order::count(),
            'orders_pending' => Order::where('status', OrderStatus::Pending)->count(),
            'orders_paid' => Order::where('status', OrderStatus::Paid)->count(),
            'orders_shipped' => Order::where('status', OrderStatus::Shipped)->count(),
            'orders_completed' => Order::where('status', OrderStatus::Completed)->count(),
            'orders_cancelled' => Order::where('status', OrderStatus::Cancelled)->count(),
            'orders_today' => Order::whereDate('created_at', $today)->count(),
            'sales_today' => round((float) Order::where('status', OrderStatus::Completed)->whereDate('created_at', $today)->sum('total_amount'), 2),
            'sales_7_days' => round((float) Order::where('status', OrderStatus::Completed)->where('created_at', '>=', $today->copy()->subDays(6))->sum('total_amount'), 2),
            'sales_total' => round((float) Order::where('status', OrderStatus::Completed)->sum('total_amount'), 2),
        ]);

        $salesTrend = collect(range(6, 0))->map(function ($daysAgo) use ($today) {
            $date = $today->copy()->subDays($daysAgo);

            return [
                'date' => $date->toDateString(),
                'label' => $date->format('D'),
                'total' => round((float) Order::where('status', OrderStatus::Completed)->whereDate('created_at', $date)->sum('total_amount'), 2),
            ];
        })->values();

        $recentOrders = Order::with(['user', 'items'])
            ->where('sale_type', 'online')
            ->orderByDesc('order_id')->take(4)->get()
            ->map(fn (Order $order) => OrderPresenter::summary($order))->values();

        $recentActivity = ActivityLog::with('user')->orderByDesc('activity_log_id')->take(6)->get()->map(fn ($log) => [
            'id' => $log->activity_log_id,
            'action' => $log->action,
            'event' => $log->event,
            'category' => $log->category,
            'subject' => $log->subject_label,
            'user' => $log->user?->full_name ?? 'System',
            'created_at' => $log->created_at?->toIso8601String(),
        ])->values();

        return response()->json([
            'generated_at' => now()->toIso8601String(),
            'summary' => $summary,
            'sales_trend' => $salesTrend,
            'recent_orders' => $recentOrders,
            'recent_activity' => $recentActivity,
            'alerts' => $this->alerts($summary),
        ]);
    }

    /** @param  \Illuminate\Database\Query\Builder  $stockTotals */
    private function variantCount($stockTotals, string $operator, $value): int
    {
        $query = DB::query()->fromSub($stockTotals, 'stock_totals');

        if ($operator === 'between') {
            return $query->whereBetween('total_remaining', $value)->count();
        }

        return $query->where('total_remaining', $operator, $value)->count();
    }

    private function alerts(array $summary): array
    {
        $alerts = [];

        if ($summary['out_of_stock'] > 0) {
            $alerts[] = [
                'severity' => 'critical',
                'title' => $summary['out_of_stock'].' variant(s) out of stock',
                'body' => 'Restock before these products disappear from the storefront.',
                'route' => '/tabs/inventory',
                'icon' => 'alert',
            ];
        }
        if ($summary['approvals_pending'] > 0) {
            $alerts[] = [
                'severity' => 'warning',
                'title' => $summary['approvals_pending'].' approval(s) waiting',
                'body' => 'Admin submissions are queued for your review.',
                'route' => '/tabs/approvals',
                'icon' => 'approval',
            ];
        }
        if ($summary['low_stock'] > 0) {
            $alerts[] = [
                'severity' => 'warning',
                'title' => $summary['low_stock'].' variant(s) low on stock',
                'body' => 'Five units or fewer remaining.',
                'route' => '/tabs/inventory?filter=low',
                'icon' => 'inventory',
            ];
        }
        if ($summary['invitations_pending'] > 0) {
            $alerts[] = [
                'severity' => 'info',
                'title' => $summary['invitations_pending'].' admin invitation(s) pending',
                'body' => 'Waiting for the recipient to set their password.',
                'route' => '/tabs/users/admins/invitations',
                'icon' => 'invitation',
            ];
        }

        return $alerts;
    }
}