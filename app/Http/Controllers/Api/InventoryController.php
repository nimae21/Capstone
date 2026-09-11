<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'filter' => ['nullable', 'in:all,low,out'],
            'search' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $stockTotals = Stock::select('product_variant_id', DB::raw('SUM(remaining_quantity) as total_remaining'))
            ->where('is_archived', false)
            ->groupBy('product_variant_id');

        $latestPrice = Stock::select('price')
            ->whereColumn('stocks.product_variant_id', 'product_variants.product_variant_id')
            ->where('stocks.is_archived', false)
            ->orderByDesc('stocks.deliver_date')
            ->limit(1);

        $query = ProductVariant::query()
            ->join('products', 'products.product_id', '=', 'product_variants.product_id')
            ->leftJoinSub($stockTotals, 'stock_totals', fn ($join) => $join->on('stock_totals.product_variant_id', '=', 'product_variants.product_variant_id'))
            ->select([
                'product_variants.product_variant_id',
                'product_variants.product_id',
                'product_variants.size',
                'product_variants.color',
                'product_variants.is_active',
                'products.product_name',
                'products.is_active as product_active',
                DB::raw('COALESCE(stock_totals.total_remaining, 0) as remaining'),
                DB::raw('('.((string) $latestPrice->toSql()).') as price'),
            ])
            ->addBinding($latestPrice->getBindings(), 'select');

        $filter = $filters['filter'] ?? 'all';
        if ($filter === 'low') {
            $query->whereBetween('stock_totals.total_remaining', [1, 5]);
        } elseif ($filter === 'out') {
            $query->whereRaw('COALESCE(stock_totals.total_remaining, 0) <= 0');
        }

        if (! empty($filters['search'])) {
            $term = trim($filters['search']);
            $query->where(fn ($q) => $q->where('products.product_name', 'like', '%'.$term.'%')
                ->orWhere('product_variants.color', 'like', '%'.$term.'%')
                ->orWhere('product_variants.size', 'like', '%'.$term.'%'));
        }

        $variants = $query->orderBy('products.product_name')->orderBy('product_variants.size')
            ->paginate(25)
            ->through(fn ($row) => [
                'id' => (int) $row->product_variant_id,
                'product_id' => (int) $row->product_id,
                'product' => $row->product_name,
                'size' => $row->size,
                'color' => $row->color,
                'price' => $row->price !== null ? round((float) $row->price, 2) : null,
                'remaining' => max(0, (int) $row->remaining),
                'value' => $row->price !== null ? round((float) $row->price * max(0, (int) $row->remaining), 2) : null,
                'state' => (int) $row->remaining <= 0 ? 'out' : ((int) $row->remaining <= 5 ? 'low' : 'ok'),
                'is_active' => (bool) $row->is_active && (bool) $row->product_active,
            ]);

        return response()->json($variants);
    }
}