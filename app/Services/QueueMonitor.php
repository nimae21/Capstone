<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class QueueMonitor
{
    public const HEARTBEAT_KEY = 'queue-worker-last-seen';

    public function beat(): void
    {
        try {
            Cache::put(
                self::HEARTBEAT_KEY,
                now()->timestamp,
                now()->addSeconds((int) config('background_jobs.heartbeat_ttl', 300)),
            );
        } catch (\Throwable $exception) {
            report($exception);
        }
    }
}
