<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Cart;

class CartItem extends Model
{
    protected $primaryKey = 'cart_item_id';

    protected $fillable = [
        'cart_id',
        'product_variant_id',
        'quantity',
        'price',
    ];

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id', 'product_variant_id');
    }

    public function cart()
{
    return $this->belongsTo(Cart::class, 'cart_id', 'cart_id');
}
}
