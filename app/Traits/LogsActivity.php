<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Str;

/**
 * Add `use LogsActivity;` to any Eloquent model to automatically record
 * created/updated/deleted events to the activity_logs table - no manual
 * logging calls needed in your controllers/services.
 *
 * Deliberately NOT applied to the Stock model: StockService::deduct()
 * updates `remaining_quantity` on every single checkout, and Eloquent
 * can't distinguish "sale deduction" from "admin correction" - both are
 * just an update to the same column. Applying this trait there would log
 * every sale as generic noise. Manual stock corrections are logged
 * explicitly, at the specific admin action that performs them, instead.
 *
 * Two optional properties a model can define to customize behavior:
 *   protected $activityLogExcept = ['some_column'];  // never log this field's value
 */
trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(function ($model) {
            $model->writeActivityLog('created');
        });

        static::updated(function ($model) {
            $changes = $model->activityLogChanges();

            // Nothing worth recording (e.g. only a timestamp touch) - skip,
            // so the log stays meaningful instead of filling with no-ops.
            if (empty($changes)) {
                return;
            }

            $model->writeActivityLog('updated', $changes);
        });

        static::deleted(function ($model) {
            $model->writeActivityLog('deleted');
        });
    }

    /**
     * Fields that never appear in the log, even as an old/new value.
     * Always includes the model's own $hidden attributes as a safety net
     * (so `password` on User can never leak into a log entry even if
     * someone forgets to also add it to $activityLogExcept), plus
     * timestamps, which aren't meaningful to audit.
     */
    protected function activityLogExcludedFields(): array
    {
        $modelExcept = property_exists($this, 'activityLogExcept') ? $this->activityLogExcept : [];

        return array_unique(array_merge(
            $this->getHidden(),
            ['created_at', 'updated_at', 'remember_token'],
            $modelExcept
        ));
    }

    protected function activityLogChanges(): array
    {
        $excluded = $this->activityLogExcludedFields();

        // getChanges() holds the NEW values of changed attributes, and
        // getOriginal() still holds the OLD values at this point - Eloquent
        // calls syncChanges() before firing 'updated', but syncOriginal()
        // only happens later in finishSave(). This ordering is what lets us
        // read both sides of the diff from inside this event.
        $changed = $this->getChanges();
        $original = $this->getOriginal();

        $result = [];

        foreach ($changed as $key => $newValue) {
            if (in_array($key, $excluded, true)) {
                continue;
            }

            $result[$key] = [
                'old' => $original[$key] ?? null,
                'new' => $newValue,
            ];
        }

        return $result;
    }

    protected function writeActivityLog(string $event, ?array $changes = null): void
    {
        ActivityLog::create([
            'user_id'      => auth()->id(),
            'action'       => Str::snake(class_basename($this)) . '.' . $event,
            'subject_type' => static::class,
            'subject_id'   => $this->getKey(),
            'changes'      => $changes,
            'ip_address'   => app()->runningInConsole() ? null : request()->ip(),
        ]);
    }
}