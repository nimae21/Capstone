<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserActivity extends Model
{
    protected $primaryKey = 'activity_id';

    protected $fillable = [
        'user_id',
        'product_id',
        'activity_type',
        'activity_window',
        'activity_count',
    ];

    protected $casts = [
        'activity_window' => 'datetime',
        'activity_count' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}
