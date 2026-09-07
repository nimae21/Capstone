<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

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
    public function orderItems()
{
    return $this->hasMany(OrderItem::class, 'product_variant_id', 'product_variant_id');
}

public function getTotalStockAttribute()
{
    return $this->stocks->sum('remaining_quantity');
}
}
