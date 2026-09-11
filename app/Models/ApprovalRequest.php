<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class ApprovalRequest extends Model
{
    use LogsActivity;

    protected $fillable = ['requester_id', 'entity_type', 'action_type', 'payload', 'status', 'reviewer_id', 'reviewed_at', 'rejection_reason', 'entity_id'];

    protected $casts = ['payload' => 'array', 'reviewed_at' => 'datetime'];

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
