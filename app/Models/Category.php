<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Category extends Model
{
    use HasFactory, LogsActivity;

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('catalog.filter-options.v1'));
        static::deleted(fn () => Cache::forget('catalog.filter-options.v1'));
    }

    protected $primaryKey = 'category_id';

    protected $fillable = [
        'category_name',
        'category_description',
        'is_active',
    ];

    /**
     * Products under this category.
     */
    public function products()
    {
        return $this->hasMany(
            Product::class,
            'category_id',
            'category_id'
        );
    }
}
