<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Stock extends Model
{
    use HasFactory;

    protected $primaryKey = 'stock_id';

    protected $fillable = [
        'product_variant_id',
        'price',
        'quantity',
        'deliver_date',
    ];
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id', 'product_variant_id');
    }

    public function movements()
    {
        return $this->hasMany(StockMovement::class, 'stock_id', 'stock_id');
    }
}