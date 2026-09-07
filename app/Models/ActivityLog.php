<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';
    protected $primaryKey = 'activity_log_id';

    protected $fillable = [
        'user_id',
        'action',
        'subject_type',
        'subject_id',
        'changes',
        'ip_address',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * The Product/Order/User/etc this log entry is about. Uses Eloquent's
     * standard morphTo naming (subject_type + subject_id columns), so it
     * works for any model without needing a relation defined per type.
     */
    public function subject()
    {
        return $this->morphTo();
    }
}