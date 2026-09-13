<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:all,in-stock,low-stock,out-stock'],
        ]);
        $stockTotals = DB::table('stocks')
            ->select('product_variant_id')
            ->selectRaw('SUM(remaining_quantity) as total_stock')
            ->groupBy('product_variant_id');
        $salesTotals = DB::table('order_items')
            ->select('product_variant_id')
            ->selectRaw('SUM(quantity) as total_sold')
            ->groupBy('product_variant_id');
        $inventoryQuery = DB::table('product_variants')
            ->join('products', 'products.product_id', '=', 'product_variants.product_id')
            ->leftJoinSub($stockTotals, 'stock_totals', function ($join) {
                $join->on('stock_totals.product_variant_id', '=', 'product_variants.product_variant_id');
            })
            ->leftJoinSub($salesTotals, 'sales_totals', function ($join) {
                $join->on('sales_totals.product_variant_id', '=', 'product_variants.product_variant_id');
            })
            ->select(
                'product_variants.product_variant_id',
                'products.product_name',
                'product_variants.size',
                'product_variants.color',
            )
            ->selectRaw('COALESCE(stock_totals.total_stock, 0) as total_stock')
            ->selectRaw('COALESCE(sales_totals.total_sold, 0) as total_sold')
            ->selectRaw('COALESCE(stock_totals.total_stock, 0) as available_stock');

        $stats = DB::query()->fromSub(clone $inventoryQuery, 'inventory')
            ->selectRaw(
                'COUNT(*) as total_products,
                 COALESCE(SUM(CASE WHEN available_stock BETWEEN 1 AND 5 THEN 1 ELSE 0 END), 0) as low_stock,
                 COALESCE(SUM(CASE WHEN available_stock <= 0 THEN 1 ELSE 0 END), 0) as out_of_stock'
            )
            ->first();

        $search = trim((string) ($filters['search'] ?? ''));
        $status = $filters['status'] ?? 'all';
        $inventory = $inventoryQuery
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nested) use ($search) {
                    $escaped = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], strtolower($search));
                    $like = "%{$escaped}%";
                    $nested->whereRaw("LOWER(products.product_name) LIKE ? ESCAPE '!'", [$like])
                        ->orWhereRaw("LOWER(product_variants.size) LIKE ? ESCAPE '!'", [$like])
                        ->orWhereRaw("LOWER(product_variants.color) LIKE ? ESCAPE '!'", [$like]);
                });
            })
            ->when($status === 'in-stock', fn ($query) => $query->whereRaw('COALESCE(stock_totals.total_stock, 0) > 5'))
            ->when($status === 'low-stock', fn ($query) => $query->whereRaw('COALESCE(stock_totals.total_stock, 0) BETWEEN 1 AND 5'))
            ->when($status === 'out-stock', fn ($query) => $query->whereRaw('COALESCE(stock_totals.total_stock, 0) <= 0'))
            ->orderBy('products.product_name')
            ->orderBy('product_variants.product_variant_id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.inventory.index', compact(
            'inventory',
        ) + [
            'totalProducts' => (int) $stats->total_products,
            'lowStock' => (int) $stats->low_stock,
            'outOfStock' => (int) $stats->out_of_stock,
        ]);
    }
}
