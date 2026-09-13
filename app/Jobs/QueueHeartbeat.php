<?php

namespace App\Jobs;

use App\Services\QueueMonitor;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class QueueHeartbeat implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 10;

    public int $uniqueFor = 120;

    public function __construct()
    {
        $this->onQueue(config('background_jobs.queue', 'background'));
    }

    public function handle(QueueMonitor $monitor): void
    {
        $monitor->beat();
    }

    public function uniqueId(): string
    {
        return 'queue-heartbeat';
    }
}
