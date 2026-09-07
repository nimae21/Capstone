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

    /**
     * The part of `action` before the dot (e.g. 'product.updated' -> 'product').
     * This IS the category - no separate column to keep in sync, and any new
     * model that starts using LogsActivity automatically gets its own tab on
     * the admin logs page without needing this list updated anywhere.
     */
    public function getCategoryAttribute(): string
    {
        return \Illuminate\Support\Str::before((string) $this->action, '.');
    }

    /**
     * The part of `action` after the dot (e.g. 'product.updated' -> 'updated').
     */
    public function getEventAttribute(): string
    {
        return \Illuminate\Support\Str::after((string) $this->action, '.');
    }

    /**
     * A human-readable name for whatever the log is about, e.g. the product's
     * name instead of just its ID. Falls back gracefully if the subject was
     * since deleted (subject_id still points to it, but the row is gone).
     */
    public function getSubjectLabelAttribute(): ?string
    {
        if (! $this->subject_id) {
            return null;
        }

        if (! $this->subject) {
            return "#{$this->subject_id} (deleted)";
        }

        foreach (['product_name', 'brand_name', 'category_name', 'shoe_type_name', 'full_name', 'email'] as $attribute) {
            if (isset($this->subject->{$attribute})) {
                return (string) $this->subject->{$attribute};
            }
        }

        return "#{$this->subject_id}";
    }
}