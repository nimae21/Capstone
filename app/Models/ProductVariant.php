<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory, LogsActivity;

    protected $primaryKey = 'product_variant_id';

    protected $fillable = [
        'product_id',
        'size',
        'color',
        'is_active',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class, 'product_variant_id', 'product_variant_id');
    }

    /**
     * Add display stock data without hydrating stock batches. FIFO deduction
     * continues to query and lock the underlying rows in StockService.
     */
    public function scopeWithStorefrontStock(Builder $query): Builder
    {
        return $query
            ->withSum([
                'stocks as available_stock' => fn ($stocks) => $stocks->where('is_archived', false),
            ], 'remaining_quantity')
            ->addSelect([
                'current_price' => Stock::query()
                    ->select('price')
                    ->whereColumn('stocks.product_variant_id', 'product_variants.product_variant_id')
                    ->where('is_archived', false)
                    ->orderByDesc('deliver_date')
                    ->orderByDesc('stock_id')
                    ->limit(1),
            ]);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'product_variant_id', 'product_variant_id');
    }

    public function getTotalStockAttribute()
    {
        return $this->stocks->sum('remaining_quantity');
    }
}
