<?php

namespace App\Console\Commands;

use App\Models\BackgroundOperation;
use App\Services\QueueMonitor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class QueueHealth extends Command
{
    protected $signature = 'queue:health {--max-age=120 : Maximum heartbeat age in seconds}';

    protected $description = 'Fail when the queue worker heartbeat is stale and report durable backlog';

    public function handle(): int
    {
        $maximumAge = max(30, (int) $this->option('max-age'));
        try {
            $lastSeen = (int) Cache::get(QueueMonitor::HEARTBEAT_KEY, 0);
        } catch (Throwable $exception) {
            report($exception);
            $lastSeen = 0;
            $this->error('The queue heartbeat cache is unavailable.');
        }
        $age = $lastSeen > 0 ? now()->timestamp - $lastSeen : null;
        $pending = BackgroundOperation::whereNull('processed_at')
            ->where('available_at', '<=', now())->count();
        $oldest = BackgroundOperation::whereNull('processed_at')
            ->where('available_at', '<=', now())->min('created_at');
        $failed = DB::table('failed_jobs')->count();

        $this->line('Due durable operations: '.$pending);
        $this->line('Oldest due operation: '.($oldest ?: 'none'));
        $this->line('Failed queue jobs: '.$failed);
        $this->line('Worker heartbeat age: '.($age === null ? 'missing' : $age.'s'));

        if ($age === null || $age > $maximumAge) {
            $this->error('Queue worker heartbeat is stale.');

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
