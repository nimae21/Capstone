<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class Product extends Model
{
    use LogsActivity;
    protected $primaryKey = 'product_id';

    protected $fillable = [
    'category_id',
    'brand_id',
    'shoe_type_id',
    'product_name',
    'product_description',
    'is_active',
    'new_arrival_until',
];

    protected $casts = [
        'new_arrival_until' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            // Explicit null lets the admin opt out. Updates never renew the tag.
            if (!array_key_exists('new_arrival_until', $product->getAttributes())) {
                $product->new_arrival_until = now()->addDays(30)->endOfDay();
            }
        });
    }

    public function getIsNewArrivalAttribute(): bool
    {
        return (bool) $this->is_active
            && $this->new_arrival_until !== null
            && $this->new_arrival_until->isFuture();
    }

    public function scopeNewArrivals(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('products.is_active', true)
            ->where('products.new_arrival_until', '>', now());
    }

    public function scopeWithDisplayPrice(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->addSelect(['display_price' => Stock::selectRaw('MIN(stocks.price)')
            ->join('product_variants', 'product_variants.product_variant_id', '=', 'stocks.product_variant_id')
            ->whereColumn('product_variants.product_id', 'products.product_id')]);
    }
    public function category()
{
    return $this->belongsTo(Category::class, 'category_id', 'category_id');
}

public function brand()
{
    return $this->belongsTo(Brand::class, 'brand_id', 'brand_id');
}

public function shoeType()
{
    return $this->belongsTo(ShoeType::class, 'shoe_type_id', 'shoe_type_id');
}

public function variants()
{
    return $this->hasMany(ProductVariant::class, 'product_id', 'product_id');
}

public function images()
{
    return $this->hasMany(ProductImage::class, 'product_id', 'product_id')->orderBy('display_order');
}
public function primaryImage()
{
    return $this->hasOne(ProductImage::class, 'product_id', 'product_id')
        ->where('is_primary', true);
}
public function interactions()
{
    return $this->hasMany(UserActivity::class, 'product_id', 'product_id');
}
}