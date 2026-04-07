<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{      
    use HasFactory;
    protected $primaryKey = 'product_variant_id';

    protected $fillable = [
        'product_id',
        'size',
        'color',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
    public function stocks()
    {
        return $this->hasMany(Stock::class, 'product_variant_id', 'product_variant_id');
    }

}
