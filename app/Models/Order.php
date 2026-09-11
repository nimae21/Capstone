<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\SaleType;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use LogsActivity;

    protected $primaryKey = 'order_id';

    protected $casts = [
        'status' => OrderStatus::class,
        'sale_type' => SaleType::class,
    ];

    protected $fillable = [
        'user_id',
        'sale_type',
        'total_amount',
        'status',
        'full_name',
        'phone_number',
        'street',
        'barangay',
        'city',
        'province',
        'postal_code',
        'latitude',
        'longitude',
        'payment_method',
        'pos_request_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'order_id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class, 'order_id', 'order_id');
    }

    public function getRouteKeyName(): string
    {
        return 'order_id';
    }
}
