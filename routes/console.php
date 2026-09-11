<?php

use App\Models\PendingRegistration;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// For local demos: php artisan schedule:work. Production needs Laravel's scheduler.
Schedule::command('mobile:send-push')
    ->everyTenSeconds()->withoutOverlapping(5);
Artisan::command('registrations:prune', function () {
    $count = PendingRegistration::where('expires_at', '<=', now())->delete();
    $this->info("Removed {$count} expired pending registrations.");
})->purpose('Remove expired temporary registrations only');
Schedule::command('registrations:prune')->hourly()->withoutOverlapping();
