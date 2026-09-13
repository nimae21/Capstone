<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckoutRetryOperation extends Model
{
    protected $fillable = [
        'payment_id',
        'operation_key',
        'source_session_id',
        'target_session_id',
        'target_payment_intent_id',
        'checkout_url',
        'status',
        'attempts',
        'last_error',
    ];

    protected $casts = ['attempts' => 'integer'];

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id', 'payment_id');
    }
}
