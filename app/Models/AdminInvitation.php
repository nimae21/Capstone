<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class AdminInvitation extends Model
{
    use LogsActivity;

    protected $fillable = ['email', 'inviter_id', 'token_hash', 'expires_at', 'accepted_at', 'accepted_user_id'];

    protected $hidden = ['token_hash'];

    protected $casts = ['expires_at' => 'datetime', 'accepted_at' => 'datetime'];
}
