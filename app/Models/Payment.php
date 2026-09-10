<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $primaryKey = 'payment_id';

    protected $fillable = [
    'order_id',
    'checkout_session_id',
    'paymongo_payment_id',
    'refund_status',
    'paymongo_refund_id',
    'refund_amount',
    'refund_request_key',
    'refund_requested_at',
    'refund_updated_at',
    'refund_error',
    'method',
    'status',
    'payment_date',
];

    protected $casts = [
        'payment_date' => 'datetime',
        'refund_requested_at' => 'datetime',
        'refund_amount' => 'integer',
        'refund_updated_at' => 'integer',
    ];

    public function getRefundLabelAttribute(): ?string
    {
        return match ($this->refund_status) {
            'pending' => 'Refund pending',
            'refunded' => 'Refunded',
            'failed' => 'Refund failed',
            default => null,
        };
    }

    public function getCanRetryRefundAttribute(): bool
    {
        return $this->refund_status === 'pending' && !$this->paymongo_refund_id
            && $this->refund_error && $this->refund_requested_at?->gt(now()->subHours(23));
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }
}
