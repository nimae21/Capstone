<?php

namespace App\Jobs;

use App\Services\ActivityTrackingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RecordUserActivities implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $userId,
        public array $productIds,
        public string $type,
        public string $occurredAt,
    ) {
        $this->afterCommit();
    }

    public function handle(ActivityTrackingService $activities): void
    {
        $activities->recordNow($this->userId, $this->productIds, $this->type, $this->occurredAt);
    }
}
