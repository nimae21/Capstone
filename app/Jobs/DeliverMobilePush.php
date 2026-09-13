<?php

namespace App\Jobs;

use App\Services\FirebasePushSender;
use App\Services\MobilePushOutbox;
use App\Services\QueueMonitor;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DeliverMobilePush implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries;

    public int $timeout;

    public int $uniqueFor = 60;

    public function __construct()
    {
        $this->tries = (int) config('background_jobs.tries', 5);
        $this->timeout = (int) config('background_jobs.push_timeout', 60);
        $this->onQueue(config('background_jobs.queue', 'background'));
        $this->afterCommit();
    }

    public function handle(
        MobilePushOutbox $outbox,
        FirebasePushSender $sender,
        QueueMonitor $monitor,
    ): void {
        $monitor->beat();
        $outbox->deliverPending($sender);
    }

    public function uniqueId(): string
    {
        return 'mobile-push-pump';
    }

    public function backoff(): array
    {
        return config('background_jobs.backoff', [10, 30, 60, 300]);
    }
}
