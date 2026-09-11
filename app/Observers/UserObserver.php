<?php

namespace App\Observers;

use App\Models\User;
use App\Services\SuperAdminNotifier;

class UserObserver
{
    public function updated(User $user): void
    {
        if (! $user->wasChanged('is_active')) {
            return;
        }

        app(SuperAdminNotifier::class)->accountStatusChanged($user, (bool) $user->is_active, auth()->user());
    }
}