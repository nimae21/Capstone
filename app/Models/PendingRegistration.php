<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingRegistration extends Model
{
    protected $fillable = ['email', 'payload', 'token_hash', 'expires_at'];

    protected $hidden = ['payload', 'token_hash'];

    protected $casts = ['payload' => 'encrypted:array', 'expires_at' => 'datetime'];
}
