<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $primaryKey = 'cart_item_id';

    protected $fillable = [
        'cart_id',
        'product_variant_id',
        'quantity',
        'price',
    ];

    /**
     * Cart that owns this item.
     */
    public function cart()
    {
        return $this->belongsTo(Cart::class, 'cart_id', 'cart_id');
    }

    /**
     * Product variant.
     */
    public function variant()
    {
        return $this->belongsTo(
            ProductVariant::class,
            'product_variant_id',
            'product_variant_id'
        );
    }
}