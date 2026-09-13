<?php

namespace App\Console\Commands;

use App\Models\UserActivity;
use Illuminate\Console\Command;

class PruneUserActivities extends Command
{
    protected $signature = 'activities:prune';

    protected $description = 'Delete recommendation activity older than the configured retention period';

    public function handle(): int
    {
        $days = (int) config('activity_tracking.retention_days', 0);
        if ($days < 1) {
            $this->info('Activity retention is disabled.');

            return self::SUCCESS;
        }

        $cutoff = now()->subDays($days);
        $batch = (int) config('activity_tracking.prune_batch', 1000);
        $deleted = 0;

        do {
            $ids = UserActivity::where('created_at', '<', $cutoff)
                ->orderBy('created_at')->orderBy('activity_id')
                ->limit($batch)->pluck('activity_id');
            $count = $ids->isEmpty() ? 0 : UserActivity::whereKey($ids)->delete();
            $deleted += $count;
        } while ($count === $batch);

        $this->info("Deleted {$deleted} expired activity row(s). Retained {$days} days.");

        return self::SUCCESS;
    }
}
