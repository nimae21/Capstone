<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// For local demos: php artisan schedule:work. Production needs Laravel's scheduler.
\Illuminate\Support\Facades\Schedule::command('mobile:send-push')
    ->everyTenSeconds()->withoutOverlapping(5);