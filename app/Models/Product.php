<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

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
            if (! array_key_exists('new_arrival_until', $product->getAttributes())) {
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

    public function scopeNewArrivals(Builder $query): Builder
    {
        return $query->where('products.is_active', true)
            ->where('products.new_arrival_until', '>', now());
    }

    public function scopeWithDisplayPrice(Builder $query): Builder
    {
        return $query->addSelect(['display_price' => Stock::selectRaw('MIN(stocks.price)')
            ->join('product_variants', 'product_variants.product_variant_id', '=', 'stocks.product_variant_id')
            ->whereColumn('product_variants.product_id', 'products.product_id')]);
    }

    public function scopeForStorefrontCards(Builder $query): Builder
    {
        return $query
            ->select([
                'products.product_id',
                'products.category_id',
                'products.brand_id',
                'products.shoe_type_id',
                'products.product_name',
                'products.product_description',
                'products.is_active',
                'products.new_arrival_until',
                'products.created_at',
            ])
            ->withDisplayPrice();
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
