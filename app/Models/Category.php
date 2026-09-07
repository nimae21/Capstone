<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class Category extends Model
{
    use HasFactory, LogsActivity;

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