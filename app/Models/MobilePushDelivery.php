<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobilePushDelivery extends Model
{
    protected $fillable = [
        'device_id', 'personal_access_token_id', 'event_key', 'kind', 'order_id',
        'notification_id', 'data', 'status', 'attempts', 'available_at', 'locked_at',
        'sent_at', 'last_error',
    ];

    protected $casts = [
        'available_at' => 'datetime',
        'locked_at' => 'datetime',
        'sent_at' => 'datetime',
        'data' => 'array',
    ];

    public function device()
    {
        return $this->belongsTo(MobilePushDevice::class, 'device_id');
    }
}