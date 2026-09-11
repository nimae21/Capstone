<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Suspending/restoring accounts. Shared by the website governance screen and
 * the mobile API so the guard rails stay identical: never yourself, and only
 * customer or operational admin accounts.
 */
class AccountStatusService
{
    public function setActive(User $target, User $actor, bool $active): User
    {
        return DB::transaction(function () use ($target, $actor, $active) {
            $locked = User::whereKey($target->id)->lockForUpdate()->firstOrFail();
            abort_unless(
                $locked->id !== $actor->id && in_array($locked->role, ['user', 'admin'], true),
                403,
                'Only other customer and admin accounts can be suspended or restored.'
            );
            $locked->forceFill(['is_active' => $active])->save();

            if (! $active) {
                $locked->tokens()->delete();
            }

            return $locked;
        });
    }
}