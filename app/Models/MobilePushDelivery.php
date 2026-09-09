<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobilePushDelivery extends Model
{
    protected $guarded = [];
    protected $casts = ['available_at' => 'datetime', 'locked_at' => 'datetime', 'sent_at' => 'datetime'];
    public function device() { return $this->belongsTo(MobilePushDevice::class, 'device_id'); }
}
