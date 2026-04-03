<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $primaryKey = 'product_id';

    protected $fillable = [
        'category_id',
        'brand_id',
        'product_name',
        'product_description',
    ];

    public function category()
{
    return $this->belongsTo(Category::class, 'category_id', 'category_id');
}

public function brand()
{
    return $this->belongsTo(Brand::class, 'brand_id', 'brand_id');
}
}
