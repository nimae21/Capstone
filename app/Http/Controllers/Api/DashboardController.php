<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Order;
use App\Models\Stock;
use App\Models\User;
use App\Support\ActivitySubjectLoader;
use App\Support\OrderPresenter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Mobile-first overview of the whole Achilles system.
 *
 * The database is remote and pooled, so every round trip costs real time: this
 * controller is deliberately written as a small fixed number of aggregate
 * queries instead of one count()/sum() per metric. Keep it that way - the
 * regression test asserts a query budget.
 */
class DashboardController extends Controller
{
    private const CACHE_KEY = 'mobile-dashboard-overview';

    /** Overview numbers change slowly; a short cache makes tab switches instant. */
    private const CACHE_SECONDS = 30;

    private const LOW_STOCK_THRESHOLD = 5;

    public function index(Request $request)
    {
        // Pull-to-refresh asks for fresh data and re-warms the cache.
        $overview = $request->boolean('fresh')
            ? tap($this->build(), fn ($data) => Cache::put(self::CACHE_KEY, $data, self::CACHE_SECONDS))
            : Cache::remember(self::CACHE_KEY, self::CACHE_SECONDS, fn () => $this->build());

        return response()->json($overview + [
            // Unread is per account, so it never goes into the shared cache.
            'badges' => [
                'unread_notifications' => $request->user()->unreadNotifications()->count(),
                'pending_approvals' => $overview['summary']['approvals_pending'],
            ],
        ]);
    }

    private function build(): array
    {
        $today = Carbon::today();
        $weekStart = $today->copy()->subDays(6);

        $orders = Order::query()->selectRaw(
            "COUNT(*) as total,
             COALESCE(SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END), 0) as pending,
             COALESCE(SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END), 0) as paid,
             COALESCE(SUM(CASE WHEN status = 'shipped' THEN 1 ELSE 0 END), 0) as shipped,
             COALESCE(SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END), 0) as completed,
             COALESCE(SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END), 0) as cancelled,
             COALESCE(SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END), 0) as today",
            [$today]
        )->first();

        $sales = Order::query()->where('status', 'completed')->selectRaw(
            'COALESCE(SUM(total_amount), 0) as total,
             COALESCE(SUM(CASE WHEN created_at >= ? THEN total_amount ELSE 0 END), 0) as today,
             COALESCE(SUM(CASE WHEN created_at >= ? THEN total_amount ELSE 0 END), 0) as last7',
            [$today, $weekStart]
        )->first();

        // One grouped query for the whole week instead of one query per day.
        $dailyRevenue = Order::query()
            ->where('status', 'completed')
            ->where('created_at', '>=', $weekStart)
            ->selectRaw('DATE(created_at) as day, COALESCE(SUM(total_amount), 0) as total')
            ->groupByRaw('DATE(created_at)')
            ->pluck('total', 'day');

        $salesTrend = collect(range(6, 0))->map(function (int $daysAgo) use ($today, $dailyRevenue) {
            $date = $today->copy()->subDays($daysAgo);
            $key = $date->toDateString();

            return [
                'date' => $key,
                'label' => $date->format('D'),
                'total' => round((float) ($dailyRevenue[$key] ?? 0), 2),
            ];
        })->values();

        // Catalog, stock and pending-queue totals in a single round trip.
        $counts = DB::selectOne(
            'select
                (select count(*) from products) as products,
                (select count(*) from product_variants) as variants,
                (select coalesce(sum(remaining_quantity), 0) from stocks where is_archived = false) as inventory_quantity,
                (select coalesce(sum(price * remaining_quantity), 0) from stocks where is_archived = false) as inventory_value,
                (select count(*) from admin_invitations where accepted_at is null and expires_at > ?) as invitations_pending,
                (select count(*) from approval_requests where status = ?) as approvals_pending',
            [now(), 'pending']
        );

        $stockTotals = Stock::query()
            ->select('product_variant_id', DB::raw('SUM(remaining_quantity) as total_remaining'))
            ->where('is_archived', false)
            ->groupBy('product_variant_id');

        $stockStates = DB::query()->fromSub($stockTotals, 'stock_totals')->selectRaw(
            'COALESCE(SUM(CASE WHEN total_remaining <= 0 THEN 1 ELSE 0 END), 0) as out_of_stock,
             COALESCE(SUM(CASE WHEN total_remaining BETWEEN 1 AND ? THEN 1 ELSE 0 END), 0) as low_stock',
            [self::LOW_STOCK_THRESHOLD]
        )->first();

        $accounts = User::query()->selectRaw(
            "COALESCE(SUM(CASE WHEN role = 'user' THEN 1 ELSE 0 END), 0) as users_total,
             COALESCE(SUM(CASE WHEN role = 'user' AND is_active = true THEN 1 ELSE 0 END), 0) as users_active,
             COALESCE(SUM(CASE WHEN role = 'user' AND is_active = false THEN 1 ELSE 0 END), 0) as users_suspended,
             COALESCE(SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END), 0) as admins_total,
             COALESCE(SUM(CASE WHEN role = 'admin' AND is_active = true THEN 1 ELSE 0 END), 0) as admins_active,
             COALESCE(SUM(CASE WHEN role = 'admin' AND is_active = false THEN 1 ELSE 0 END), 0) as admins_suspended"
        )->first();

        $summary = [
            'products' => (int) $counts->products,
            'variants' => (int) $counts->variants,
            'inventory_quantity' => (int) $counts->inventory_quantity,
            'inventory_value' => round((float) $counts->inventory_value, 2),
            'out_of_stock' => (int) $stockStates->out_of_stock,
            'low_stock' => (int) $stockStates->low_stock,
            'users_total' => (int) $accounts->users_total,
            'users_active' => (int) $accounts->users_active,
            'users_suspended' => (int) $accounts->users_suspended,
            'admins_total' => (int) $accounts->admins_total,
            'admins_active' => (int) $accounts->admins_active,
            'admins_suspended' => (int) $accounts->admins_suspended,
            'invitations_pending' => (int) $counts->invitations_pending,
            'approvals_pending' => (int) $counts->approvals_pending,
            'orders_total' => (int) $orders->total,
            'orders_pending' => (int) $orders->pending,
            'orders_paid' => (int) $orders->paid,
            'orders_shipped' => (int) $orders->shipped,
            'orders_completed' => (int) $orders->completed,
            'orders_cancelled' => (int) $orders->cancelled,
            'orders_today' => (int) $orders->today,
            'sales_today' => round((float) $sales->today, 2),
            'sales_7_days' => round((float) $sales->last7, 2),
            'sales_total' => round((float) $sales->total, 2),
        ];

        $recentOrders = Order::with(['user', 'items'])
            ->where('sale_type', 'online')
            ->orderByDesc('order_id')->take(4)->get()
            ->map(fn (Order $order) => OrderPresenter::summary($order))->values();

        $recentLogs = ActivityLog::with('user')->orderByDesc('activity_log_id')->take(6)->get();
        ActivitySubjectLoader::prime($recentLogs);

        $recentActivity = $recentLogs
            ->map(fn (ActivityLog $log) => [
                'id' => $log->activity_log_id,
                'action' => $log->action,
                'event' => $log->event,
                'category' => $log->category,
                'subject' => $log->subject_label,
                'user' => $log->user?->full_name ?? 'System',
                'created_at' => $log->created_at?->toIso8601String(),
            ])->values();

        return [
            'generated_at' => now()->toIso8601String(),
            'summary' => $summary,
            'sales_trend' => $salesTrend,
            'recent_orders' => $recentOrders,
            'recent_activity' => $recentActivity,
            'alerts' => $this->alerts($summary),
        ];
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