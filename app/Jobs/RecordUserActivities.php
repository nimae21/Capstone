<?php

namespace App\Jobs;

use App\Models\BackgroundOperation;
use App\Services\ActivityTrackingService;
use App\Services\QueueMonitor;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RecordUserActivities implements ShouldBeEncrypted, ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries;

    public int $timeout;

    public bool $failOnTimeout = true;

    public int $uniqueFor;

    public ?string $operationKey = null;

    public function __construct(
        public int $userId,
        public array $productIds,
        public string $type,
        public string $occurredAt,
        ?string $operationKey = null,
    ) {
        $this->operationKey = $operationKey;
        $this->tries = (int) config('background_jobs.tries', 5);
        $this->timeout = (int) config('background_jobs.activity_timeout', 30);
        $this->uniqueFor = (int) config('background_jobs.unique_for', 600);
        $this->onQueue(config('background_jobs.queue', 'background'));
        $this->afterCommit();
    }

    public function handle(ActivityTrackingService $activities, QueueMonitor $monitor): void
    {
        $monitor->beat();

        if ($this->operationKey) {
            $activities->processOperation($this->operationKey);

            return;
        }

        // Compatibility for jobs serialized before durable activity operations
        // were introduced. New jobs always carry an operation key.
        $activities->recordNow($this->userId, $this->productIds, $this->type, $this->occurredAt);
    }

    public function uniqueId(): string
    {
        return $this->operationKey ?? hash('sha256', serialize([
            $this->userId, $this->productIds, $this->type, $this->occurredAt,
        ]));
    }

    public function backoff(): array
    {
        return config('background_jobs.backoff', [10, 30, 60, 300]);
    }

    public function failed(?\Throwable $exception): void
    {
        if (! $this->operationKey) {
            return;
        }

        BackgroundOperation::where('operation_key', $this->operationKey)->update([
            'available_at' => now()->addMinutes(5),
            'last_error' => substr($exception?->getMessage() ?? 'activity_job_failed', 0, 255),
        ]);
    }
}
