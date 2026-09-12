<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;

/**
 * Resolves the "subject" of many audit-log rows in a handful of queries.
 *
 * `ActivityLog::subject()` is a morphTo. Laravel's eager loading cannot handle
 * the rows written without a subject (failed logins have a null subject_type)
 * and throws, while lazy loading costs one query per row. This primes the
 * relation in bulk instead, so the label logic stays on the model and the page
 * costs one query per distinct subject type.
 */
class ActivitySubjectLoader
{
    /** @param  iterable<\App\Models\ActivityLog>  $logs */
    public static function prime(iterable $logs): void
    {
        $groups = [];

        foreach ($logs as $log) {
            if ($log->subject_type && $log->subject_id) {
                $groups[$log->subject_type][] = $log->subject_id;
            }
        }

        $subjects = [];

        foreach ($groups as $type => $ids) {
            if (! is_string($type) || ! class_exists($type) || ! is_subclass_of($type, Model::class)) {
                continue;
            }

            $model = new $type;
            $key = $model->getKeyName();

            foreach ($model->newQuery()->whereIn($key, array_unique($ids))->get() as $subject) {
                $subjects[$type.':'.$subject->getKey()] = $subject;
            }
        }

        foreach ($logs as $log) {
            if (! $log->subject_id) {
                $log->setRelation('subject', null);

                continue;
            }

            // A missing entry means the record was deleted; the model's
            // subject_label accessor already renders that case.
            $log->setRelation('subject', $subjects[$log->subject_type.':'.$log->subject_id] ?? null);
        }
    }
}