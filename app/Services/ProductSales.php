<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class ProductSales
{
    public static function totals(): Builder
    {
        return DB::table('order_items')
            ->join('product_variants', 'product_variants.product_variant_id', '=', 'order_items.product_variant_id')
            ->select('product_variants.product_id')
            ->selectRaw('SUM(order_items.quantity) as total_sold')
            ->groupBy('product_variants.product_id')
            ->havingRaw('SUM(order_items.quantity) > 0');
    }
}
