<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\PersonalAccessToken;

class MobilePushDevice extends Model
{
    protected $fillable = [
        'installation_id', 'user_id', 'personal_access_token_id', 'token',
        'token_hash', 'enabled', 'last_seen_at',
    ];

    protected $hidden = ['token', 'token_hash'];

    protected $casts = ['token' => 'encrypted', 'enabled' => 'boolean', 'personal_access_token_id' => 'integer', 'last_seen_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function accessToken()
    {
        return $this->belongsTo(PersonalAccessToken::class, 'personal_access_token_id');
    }

    /**
     * A device only receives Achilles alerts while it belongs to an active
     * Super Admin with a live mobile session. Admins and customers are never
     * eligible, even if an old registration row still exists.
     */
    public function scopeEligible(Builder $query): Builder
    {
        return $query->where('enabled', true)
            ->where('last_seen_at', '>', now()->subDays(config('mobile_push.device_lifetime_days', 30)))
            ->whereHas('user', fn ($q) => $q->where('role', 'super_admin')->where('is_active', true))
            ->whereHas('accessToken', function ($q) {
                $q->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
                if ($expiration = config('sanctum.expiration')) {
                    $q->where('created_at', '>', now()->subMinutes($expiration));
                }
            });
    }
}