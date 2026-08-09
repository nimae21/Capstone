<?php

namespace App\Traits;

trait HasCaseInsensitiveUniqueName
{
    /**
     * Abort with a validation error if a record with this name
     * already exists (case-insensitive), optionally excluding
     * the current record's own ID when updating.
     */
    protected function abortIfDuplicateName(
        string $modelClass,
        string $nameColumn,
        string $value,
        ?int $excludeId = null,
        ?string $primaryKey = null
    ): void {
        $query = $modelClass::whereRaw(
            "LOWER({$nameColumn}) = ?",
            [strtolower($value)]
        );

        if ($excludeId !== null && $primaryKey !== null) {
            $query->where($primaryKey, '!=', $excludeId);
        }

        if ($query->exists()) {
            $label = str($nameColumn)->replace('_', ' ')->title();

            abort(
                back()
                    ->withErrors([$nameColumn => "This {$label} already exists."])
                    ->withInput()
            );
        }
    }
}