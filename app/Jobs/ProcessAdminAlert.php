<?php

namespace App\Jobs;

use App\Models\BackgroundOperation;
use App\Services\QueueMonitor;
use App\Services\ReliableJobDispatcher;
use App\Services\SuperAdminNotifier;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class ProcessAdminAlert implements ShouldBeEncrypted, ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries;

    public int $timeout;

    public bool $failOnTimeout = true;

    public int $uniqueFor;

    public function __construct(public string $operationKey)
    {
        $this->tries = (int) config('background_jobs.tries', 5);
        $this->timeout = (int) config('background_jobs.notification_timeout', 45);
        $this->uniqueFor = (int) config('background_jobs.unique_for', 600);
        $this->onQueue(config('background_jobs.queue', 'background'));
        $this->afterCommit();
    }

    public function handle(
        SuperAdminNotifier $notifier,
        ReliableJobDispatcher $dispatcher,
        QueueMonitor $monitor,
    ): void {
        $monitor->beat();

        try {
            $hasRecipients = $notifier->materializeOperation($this->operationKey);
        } catch (\Throwable $exception) {
            BackgroundOperation::where('operation_key', $this->operationKey)->update([
                'attempts' => DB::raw('attempts + 1'),
                'available_at' => now()->addSeconds($this->backoff()[0] ?? 60),
                'last_error' => substr($exception->getMessage(), 0, 255),
            ]);
            throw $exception;
        }

        if ($hasRecipients && config('mobile_push.enabled') && config('mobile_push.auto_dispatch', true)) {
            $dispatcher->dispatch(new DeliverMobilePush);
        }
    }

    public function uniqueId(): string
    {
        return $this->operationKey;
    }

    public function backoff(): array
    {
        return config('background_jobs.backoff', [10, 30, 60, 300]);
    }

    public function failed(?\Throwable $exception): void
    {
        BackgroundOperation::where('operation_key', $this->operationKey)->update([
            'available_at' => now()->addMinutes(5),
            'last_error' => substr($exception?->getMessage() ?? 'notification_job_failed', 0, 255),
        ]);
    }
}
