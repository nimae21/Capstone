<?php

namespace App\Listeners;

use App\Models\ActivityLog;
use App\Services\SuperAdminNotifier;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Cache;

class LogAuthenticationActivity
{
    /** Failed logins for one address before the Super Admin is alerted. */
    private const FAILED_LOGIN_ALERT_THRESHOLD = 5;

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
        $email = (string) ($event->credentials['email'] ?? '');

        ActivityLog::create([
            'user_id'      => null,
            'action'       => 'auth.failed',
            'subject_type' => null,
            'subject_id'   => null,
            'changes'      => ['attempted_email' => $email ?: null],
            'ip_address'   => request()->ip(),
        ]);

        $this->alertOnRepeatedFailures($email);
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

    /**
     * Bounded brute-force signal: one alert per email per 30 minutes, raised at
     * the 5th failure (and every 5 after that) inside a 15 minute window.
     */
    private function alertOnRepeatedFailures(string $email): void
    {
        if ($email === '') {
            return;
        }

        $key = 'failed-logins:'.hash('sha256', strtolower(trim($email)));
        $count = (int) Cache::get($key, 0) + 1;
        Cache::put($key, $count, now()->addMinutes(15));

        if ($count < self::FAILED_LOGIN_ALERT_THRESHOLD || $count % self::FAILED_LOGIN_ALERT_THRESHOLD !== 0) {
            return;
        }

        if (! Cache::add('failed-login-alerted:'.$key, true, now()->addMinutes(30))) {
            return;
        }

        app(SuperAdminNotifier::class)->securityAlert(
            'Repeated failed logins',
            $count.' failed sign-in attempts for '.$email.' in the last 15 minutes.',
            ['email' => $email, 'attempts' => $count, 'ip_address' => request()->ip()],
        );
    }
}