<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class ShoeType extends Model
{
    use LogsActivity;
    protected $primaryKey = 'shoe_type_id';

protected $fillable = [
    'shoe_type_name',
    'description',
    'display_order',
    'is_active'
];

public function products()
{
    return $this->hasMany(Product::class,'shoe_type_id','shoe_type_id');
}
}