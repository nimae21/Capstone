<?php

namespace App\Listeners;

use App\Models\ActivityLog;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Registered;

class LogAuthenticationActivity
{
    public function handleLogin(Login $event): void
    {
        ActivityLog::create([
            'user_id'      => $event->user->id,
            'action'       => 'auth.login',
            'subject_type' => get_class($event->user),
            'subject_id'   => $event->user->id,
            'ip_address'   => request()->ip(),
        ]);
    }

    public function handleLogout(Logout $event): void
    {
        // $event->user can be null if the session had already expired
        if (! $event->user) {
            return;
        }

        ActivityLog::create([
            'user_id'      => $event->user->id,
            'action'       => 'auth.logout',
            'subject_type' => get_class($event->user),
            'subject_id'   => $event->user->id,
            'ip_address'   => request()->ip(),
        ]);
    }

    public function handleFailed(Failed $event): void
    {
        // No user_id by definition here - the login didn't succeed. We log
        // the attempted email instead so repeated-failure patterns (a sign
        // of a brute-force attempt) are still visible in the audit trail.
        ActivityLog::create([
            'user_id'      => null,
            'action'       => 'auth.failed',
            'subject_type' => null,
            'subject_id'   => null,
            'changes'      => ['attempted_email' => $event->credentials['email'] ?? null],
            'ip_address'   => request()->ip(),
        ]);
    }

    public function handleRegistered(Registered $event): void
    {
        ActivityLog::create([
            'user_id'      => $event->user->id,
            'action'       => 'auth.registered',
            'subject_type' => get_class($event->user),
            'subject_id'   => $event->user->id,
            'ip_address'   => request()->ip(),
        ]);
    }
}