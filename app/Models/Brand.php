<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class Brand extends Model
{
    use HasFactory, LogsActivity;

    protected static function booted(): void
    {
        // Refresh dropdowns after creates, edits, activation changes or deletes.
        static::saved(fn () => \Illuminate\Support\Facades\Cache::forget('catalog.filter-options.v1'));
        static::deleted(fn () => \Illuminate\Support\Facades\Cache::forget('catalog.filter-options.v1'));
    }
    protected $primaryKey = 'brand_id';

    protected $fillable = [
        'brand_name',
        'is_active',
    ];

    /**
     * Products under this brand.
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'brand_id', 'brand_id');
    }
}