<?php

use App\Jobs\QueueHeartbeat;
use App\Models\PendingRegistration;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// For local demos: php artisan schedule:work. Production needs Laravel's scheduler.
Schedule::command('mobile:send-push')
    ->everyMinute()->withoutOverlapping(5);
Schedule::job(new QueueHeartbeat)
    ->everyMinute()->withoutOverlapping(5);
Schedule::command('background:reconcile')
    ->everyMinute()->withoutOverlapping(5);
Artisan::command('registrations:prune', function () {
    $count = PendingRegistration::where('expires_at', '<=', now())->delete();
    $this->info("Removed {$count} expired pending registrations.");
})->purpose('Remove expired temporary registrations only');
Schedule::command('registrations:prune')->hourly()->withoutOverlapping();
Schedule::command('checkout-retries:recover')
    ->everyMinute()->withoutOverlapping(5);
Schedule::command('activities:prune')
    ->daily()->withoutOverlapping()
    ->when(fn () => (int) config('activity_tracking.retention_days') > 0);
Schedule::command('queue:prune-failed --hours='.(int) config('background_jobs.failed_retention_hours'))
    ->daily()->withoutOverlapping()
    ->when(fn () => (int) config('background_jobs.failed_retention_hours') > 0);
