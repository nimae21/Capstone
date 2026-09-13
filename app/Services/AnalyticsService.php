<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Stock;
use App\Models\User;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class AnalyticsService
{
    public const VERSION_KEY = 'analytics.data.version';

    public function dashboard(): array
    {
        $now = CarbonImmutable::now();
        $version = $this->version();
        $key = "analytics.dashboard.v1.{$version}.".$now->format('Y-m-d');

        return $this->remember($key, (int) config('analytics.dashboard_ttl'), function () use ($now): array {
            $data = $this->overview($now);
            $stockTotals = $this->stockTotals();

            $data['lowStockProducts'] = DB::table('product_variants')
                ->join('products', 'products.product_id', '=', 'product_variants.product_id')
                ->leftJoinSub($stockTotals, 'stock_totals', function ($join) {
                    $join->on('stock_totals.product_variant_id', '=', 'product_variants.product_variant_id');
                })
                ->select(
                    'products.product_name',
                    'product_variants.size as variant_size',
                    'product_variants.color as variant_color',
                )
                ->selectRaw('COALESCE(stock_totals.total_remaining, 0) as available_stock')
                ->whereBetween('stock_totals.total_remaining', [1, 5])
                ->orderBy('available_stock')
                ->orderBy('product_variants.product_variant_id')
                ->limit(5)
                ->get();

            $data['recentOrders'] = Order::query()
                ->select('order_id', 'user_id', 'total_amount', 'status', 'created_at')
                ->with('user:id,first_name,last_name')
                ->latest()
                ->limit(5)
                ->get();

            return $data;
        });
    }

    public function api(): array
    {
        $now = CarbonImmutable::now();
        $version = $this->version();
        $key = "analytics.api.v1.{$version}.".$now->format('Y-m-d');

        return $this->remember($key, (int) config('analytics.api_ttl'), function () use ($now): array {
            $data = $this->overview($now);
            $monthly = $this->monthlyTrend($now->year);

            return [
                'summary' => [
                    'total_revenue' => $data['totalRevenue'],
                    'total_orders' => $data['totalOrders'],
                    'total_customers' => $data['totalCustomers'],
                    'pending_orders' => $data['pendingOrders'],
                    'low_stock' => $data['lowStockItems'],
                ],
                'status_counts' => $data['statusCounts']->map(fn (Order $row) => [
                    'status' => $row->status->label(),
                    'total' => (int) $row->total,
                ])->values(),
                'best_sellers' => $data['topProducts']->map(fn ($row) => [
                    'product_name' => $row->product_name,
                    'total_sold' => $row->total_sold,
                ])->values(),
                'monthly_revenue' => $monthly->map(fn ($row) => [
                    'month' => CarbonImmutable::create()->month((int) $row->month)->format('M'),
                    'total' => $row->total_sales,
                ])->values(),
                'last_7_days' => collect($data['chartLabels'])->map(
                    fn (string $label, int $index) => [
                        'date' => $label,
                        'total' => $data['chartData'][$index],
                    ]
                )->values(),
            ];
        });
    }

    public function report(int $selectedYear): array
    {
        $now = CarbonImmutable::now();
        $version = $this->version();
        $key = "analytics.report.v1.{$version}.year.{$selectedYear}";

        return $this->remember($key, (int) config('analytics.report_ttl'), function () use ($now, $selectedYear): array {
            $monthStart = $now->startOfMonth();
            $nextMonthStart = $monthStart->addMonth();
            $counts = DB::query()
                ->selectSub(Product::query()->selectRaw('COUNT(*)'), 'total_products')
                ->selectSub(ProductVariant::query()->selectRaw('COUNT(*)'), 'total_variants')
                ->selectSub(User::query()->where('role', 'user')->selectRaw('COUNT(*)'), 'total_customers')
                ->first();

            $stock = Stock::query()->selectRaw(
                'COALESCE(SUM(remaining_quantity), 0) as total_inventory,
                 COALESCE(SUM(price * remaining_quantity), 0) as inventory_value,
                 COALESCE(SUM(CASE WHEN remaining_quantity = 0 THEN 1 ELSE 0 END), 0) as out_of_stock,
                 COALESCE(SUM(CASE WHEN remaining_quantity > 0 AND remaining_quantity <= 5 THEN 1 ELSE 0 END), 0) as low_stock'
            )->first();

            $ordersByStatus = Order::query()
                ->select('status')
                ->selectRaw('COUNT(*) as total')
                ->selectRaw('COALESCE(SUM(total_amount), 0) as total_sales')
                ->selectRaw('COALESCE(AVG(total_amount), 0) as average_order_value')
                ->selectRaw(
                    'COALESCE(SUM(CASE WHEN created_at >= ? AND created_at < ? THEN total_amount ELSE 0 END), 0) as current_month_sales',
                    [$monthStart, $nextMonthStart],
                )
                ->groupBy('status')
                ->get();

            $completed = $ordersByStatus->first(
                fn (Order $row) => $row->status === OrderStatus::Completed
            );
            $dateExpression = $this->dateExpression('created_at');
            $salesByDate = Order::query()
                ->selectRaw("{$dateExpression} as date, SUM(total_amount) as total_sales")
                ->where('status', OrderStatus::Completed)
                ->groupByRaw($dateExpression)
                ->orderBy('date')
                ->get();

            $topCustomers = DB::table('orders')
                ->leftJoin('users', 'users.id', '=', 'orders.user_id')
                ->select('orders.user_id', 'users.first_name', 'users.last_name')
                ->selectRaw('SUM(orders.total_amount) as total_spent')
                ->where('orders.status', OrderStatus::Completed->value)
                ->groupBy('orders.user_id', 'users.first_name', 'users.last_name')
                ->orderByDesc('total_spent')
                ->orderBy('orders.user_id')
                ->limit(5)
                ->get()
                ->each(function ($row): void {
                    $row->customer_name = trim(($row->first_name ?? '').' '.($row->last_name ?? '')) ?: 'Guest User';
                });

            $bestSellingProducts = DB::table('order_items')
                ->join('product_variants', 'product_variants.product_variant_id', '=', 'order_items.product_variant_id')
                ->join('products', 'products.product_id', '=', 'product_variants.product_id')
                ->select(
                    'product_variants.product_variant_id',
                    'products.product_name',
                    'product_variants.color',
                    'product_variants.size',
                )
                ->selectRaw('SUM(order_items.quantity) as total_sold')
                ->groupBy(
                    'product_variants.product_variant_id',
                    'products.product_name',
                    'product_variants.color',
                    'product_variants.size',
                )
                ->orderByDesc('total_sold')
                ->orderBy('product_variants.product_variant_id')
                ->limit((int) config('analytics.report_ranking_limit'))
                ->get();

            $monthlyTrend = $this->monthlyTrend($selectedYear)->keyBy(
                fn ($row) => (int) $row->month
            );
            $monthlyLabels = [];
            $monthlySalesData = [];

            for ($month = 1; $month <= 12; $month++) {
                $monthlyLabels[] = CarbonImmutable::create()->month($month)->format('M');
                $monthlySalesData[] = (float) ($monthlyTrend->get($month)?->total_sales ?? 0);
            }

            $yearExpression = $this->yearExpression('created_at');
            $yearlyTrend = Order::query()
                ->selectRaw("{$yearExpression} as year, SUM(total_amount) as total_sales")
                ->where('status', OrderStatus::Completed)
                ->groupByRaw($yearExpression)
                ->orderBy('year')
                ->get()
                ->each(function (Order $row): void {
                    $row->year = (int) $row->year;
                });
            $availableYears = $yearlyTrend->pluck('year')->sortDesc()->values();

            $salesByProvince = Order::query()
                ->select('province')
                ->selectRaw('COUNT(*) as order_count, SUM(total_amount) as total_sales')
                ->where('status', OrderStatus::Completed)
                ->groupBy('province')
                ->orderByDesc('order_count')
                ->orderBy('province')
                ->get();

            $salesByCity = Order::query()
                ->select('city', 'province')
                ->selectRaw('COUNT(*) as order_count, SUM(total_amount) as total_sales')
                ->where('status', OrderStatus::Completed)
                ->groupBy('city', 'province')
                ->orderByDesc('order_count')
                ->orderBy('city')
                ->limit(10)
                ->get();

            return [
                'totalProducts' => (int) $counts->total_products,
                'totalVariants' => (int) $counts->total_variants,
                'totalCustomers' => (int) $counts->total_customers,
                'totalInventory' => (int) $stock->total_inventory,
                'inventoryValue' => (float) $stock->inventory_value,
                'totalSales' => (float) ($completed?->total_sales ?? 0),
                'totalOrders' => $ordersByStatus->sum(fn (Order $row) => (int) $row->total),
                'averageOrderValue' => (float) ($completed?->average_order_value ?? 0),
                'monthlySales' => (float) ($completed?->current_month_sales ?? 0),
                'outOfStock' => (int) $stock->out_of_stock,
                'lowStock' => (int) $stock->low_stock,
                'ordersByStatus' => $ordersByStatus,
                'salesByDate' => $salesByDate,
                'topCustomers' => $topCustomers,
                'bestSellingProducts' => $bestSellingProducts,
                'completedCount' => (int) ($completed?->total ?? 0),
                'selectedYear' => $selectedYear,
                'monthlyLabels' => $monthlyLabels,
                'monthlySalesData' => $monthlySalesData,
                'yearlyTrend' => $yearlyTrend,
                'availableYears' => $availableYears,
                'salesByProvince' => $salesByProvince,
                'salesByCity' => $salesByCity,
            ];
        });
    }

    public function invalidate(): void
    {
        try {
            Cache::forever(self::VERSION_KEY, (string) Str::uuid());
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private function overview(CarbonImmutable $now): array
    {
        $version = $this->version();
        $key = "analytics.overview.v1.{$version}.".$now->format('Y-m-d');

        return $this->remember($key, (int) config('analytics.dashboard_ttl'), function () use ($now): array {
            $monthStart = $now->startOfMonth();
            $nextMonthStart = $monthStart->addMonth();
            $lastMonthStart = $monthStart->subMonth();
            $counts = DB::query()
                ->selectSub(Product::query()->selectRaw('COUNT(*)'), 'total_products')
                ->selectSub(
                    Product::query()->where('created_at', '>=', $monthStart)
                        ->where('created_at', '<', $nextMonthStart)->selectRaw('COUNT(*)'),
                    'new_products',
                )
                ->selectSub(ProductVariant::query()->selectRaw('COUNT(*)'), 'total_variants')
                ->selectSub(User::query()->selectRaw('COUNT(*)'), 'total_users')
                ->selectSub(User::query()->where('role', 'user')->selectRaw('COUNT(*)'), 'total_customers')
                ->selectSub(
                    User::query()->where('created_at', '>=', $monthStart)
                        ->where('created_at', '<', $nextMonthStart)->selectRaw('COUNT(*)'),
                    'new_users',
                )
                ->first();

            $stock = Stock::query()
                ->selectRaw(
                    'COALESCE(SUM(remaining_quantity), 0) as total_inventory,
                     COALESCE(SUM(price * remaining_quantity), 0) as inventory_value'
                )
                ->first();
            $stockStatus = DB::query()->fromSub($this->stockTotals(), 'stock_totals')
                ->selectRaw(
                    'COALESCE(SUM(CASE WHEN total_remaining <= 0 THEN 1 ELSE 0 END), 0) as out_of_stock,
                     COALESCE(SUM(CASE WHEN total_remaining BETWEEN 1 AND 5 THEN 1 ELSE 0 END), 0) as low_stock'
                )
                ->first();

            $statusCounts = Order::query()
                ->select('status')
                ->selectRaw('COUNT(*) as total, COALESCE(SUM(total_amount), 0) as status_sales')
                ->selectRaw(
                    'SUM(CASE WHEN created_at >= ? AND created_at < ? THEN 1 ELSE 0 END) as current_month_orders',
                    [$monthStart, $nextMonthStart],
                )
                ->selectRaw(
                    'SUM(CASE WHEN created_at >= ? AND created_at < ? THEN 1 ELSE 0 END) as last_month_orders',
                    [$lastMonthStart, $monthStart],
                )
                ->groupBy('status')
                ->get();
            $totalOrders = $statusCounts->sum(fn (Order $row) => (int) $row->total);
            $currentMonthOrders = $statusCounts->sum(fn (Order $row) => (int) $row->current_month_orders);
            $lastMonthOrders = $statusCounts->sum(fn (Order $row) => (int) $row->last_month_orders);
            $completed = $statusCounts->first(fn (Order $row) => $row->status === OrderStatus::Completed);
            $paid = $statusCounts->first(fn (Order $row) => $row->status === OrderStatus::Paid);

            $dailyRevenue = $this->dailyRevenue($now);
            $topProducts = DB::query()->fromSub(ProductSales::totals(), 'sales')
                ->join('products', 'products.product_id', '=', 'sales.product_id')
                ->select('products.product_id', 'products.product_name', 'sales.total_sold')
                ->orderByDesc('total_sold')
                ->orderBy('products.product_id')
                ->limit((int) config('analytics.ranking_limit'))
                ->get();
            $topShoeTypes = $this->rankedAttribute('shoe_types', 'shoe_type_id', 'shoe_type_name', 5);

            return [
                'totalProducts' => (int) $counts->total_products,
                'newProducts' => (int) $counts->new_products,
                'totalVariants' => (int) $counts->total_variants,
                'totalInventory' => (int) $stock->total_inventory,
                'inventoryValue' => (float) $stock->inventory_value,
                'outOfStock' => (int) $stockStatus->out_of_stock,
                'totalOrders' => $totalOrders,
                'ordersGrowth' => $lastMonthOrders > 0
                    ? (($currentMonthOrders - $lastMonthOrders) / $lastMonthOrders) * 100
                    : 0,
                'totalUsers' => (int) $counts->total_users,
                'totalCustomers' => (int) $counts->total_customers,
                'newUsers' => (int) $counts->new_users,
                'lowStockItems' => (int) $stockStatus->low_stock,
                'chartLabels' => $dailyRevenue['labels'],
                'chartData' => $dailyRevenue['data'],
                'statusCounts' => $statusCounts,
                'statusLabels' => $statusCounts->map(fn (Order $row) => $row->status->label())->values(),
                'statusCountsData' => $statusCounts->pluck('total')->map(fn ($total) => (int) $total)->values(),
                'topProducts' => $topProducts,
                'topShoeTypes' => $topShoeTypes,
                'topSize' => $this->rankedSize(),
                'topBrand' => $this->rankedAttribute('brands', 'brand_id', 'brand_name', 1)->first(),
                'topType' => $topShoeTypes->first(),
                'topCategory' => $this->rankedAttribute('categories', 'category_id', 'category_name', 1)->first(),
                'totalRevenue' => (float) ($completed?->status_sales ?? 0),
                'pendingOrders' => (int) ($paid?->total ?? 0),
            ];
        });
    }

    private function dailyRevenue(CarbonImmutable $now): array
    {
        $start = $now->startOfDay()->subDays(6);
        $end = $now->startOfDay()->addDay();
        $dateExpression = $this->dateExpression('created_at');
        $totals = Order::query()
            ->selectRaw("{$dateExpression} as revenue_date, SUM(total_amount) as total")
            ->where('status', OrderStatus::Completed)
            ->where('created_at', '>=', $start)
            ->where('created_at', '<', $end)
            ->groupByRaw($dateExpression)
            ->pluck('total', 'revenue_date');
        $labels = [];
        $data = [];

        foreach (range(6, 0) as $daysAgo) {
            $date = $now->startOfDay()->subDays($daysAgo);
            $labels[] = $date->format('D, M d');
            $data[] = (float) ($totals->get($date->toDateString()) ?? 0);
        }

        return compact('labels', 'data');
    }

    private function monthlyTrend(int $year): Collection
    {
        $start = CarbonImmutable::create($year, 1, 1)->startOfDay();
        $end = $start->addYear();
        $monthExpression = $this->monthExpression('created_at');

        return Order::query()
            ->selectRaw("{$monthExpression} as month, SUM(total_amount) as total_sales")
            ->where('status', OrderStatus::Completed)
            ->where('created_at', '>=', $start)
            ->where('created_at', '<', $end)
            ->groupByRaw($monthExpression)
            ->orderByRaw($monthExpression)
            ->get();
    }

    private function rankedSize(): ?object
    {
        return DB::table('order_items')
            ->join('product_variants', 'product_variants.product_variant_id', '=', 'order_items.product_variant_id')
            ->select('product_variants.size')
            ->selectRaw('SUM(order_items.quantity) as total_sold')
            ->groupBy('product_variants.size')
            ->orderByDesc('total_sold')
            ->orderBy('product_variants.size')
            ->first();
    }

    private function rankedAttribute(
        string $table,
        string $idColumn,
        string $labelColumn,
        int $limit,
    ): Collection {
        return DB::table('order_items')
            ->join('product_variants', 'product_variants.product_variant_id', '=', 'order_items.product_variant_id')
            ->join('products', 'products.product_id', '=', 'product_variants.product_id')
            ->join($table, "{$table}.{$idColumn}", '=', "products.{$idColumn}")
            ->select("{$table}.{$labelColumn}")
            ->selectRaw('SUM(order_items.quantity) as total_sold')
            ->groupBy("{$table}.{$idColumn}", "{$table}.{$labelColumn}")
            ->orderByDesc('total_sold')
            ->orderBy("{$table}.{$idColumn}")
            ->limit($limit)
            ->get();
    }

    private function stockTotals()
    {
        return Stock::query()
            ->select('product_variant_id')
            ->selectRaw('SUM(remaining_quantity) as total_remaining')
            ->groupBy('product_variant_id');
    }

    private function dateExpression(string $column): string
    {
        return match (DB::connection()->getDriverName()) {
            'pgsql' => "CAST({$column} AS DATE)",
            'sqlite' => "date({$column})",
            default => "DATE({$column})",
        };
    }

    private function monthExpression(string $column): string
    {
        return match (DB::connection()->getDriverName()) {
            'pgsql' => "EXTRACT(MONTH FROM {$column})",
            'sqlite' => "CAST(strftime('%m', {$column}) AS INTEGER)",
            default => "MONTH({$column})",
        };
    }

    private function yearExpression(string $column): string
    {
        return match (DB::connection()->getDriverName()) {
            'pgsql' => "EXTRACT(YEAR FROM {$column})",
            'sqlite' => "CAST(strftime('%Y', {$column}) AS INTEGER)",
            default => "YEAR({$column})",
        };
    }

    private function version(): string
    {
        try {
            return (string) Cache::rememberForever(self::VERSION_KEY, fn () => (string) Str::uuid());
        } catch (Throwable $exception) {
            report($exception);

            return 'uncached';
        }
    }

    private function remember(string $key, int $seconds, Closure $callback): mixed
    {
        $missing = new \stdClass;

        try {
            $cached = Cache::get($key, $missing);

            if ($cached !== $missing) {
                return $cached;
            }
        } catch (Throwable $exception) {
            report($exception);

            return $callback();
        }

        $value = $callback();

        try {
            Cache::put($key, $value, now()->addSeconds($seconds));
        } catch (Throwable $exception) {
            report($exception);
        }

        return $value;
    }
}
