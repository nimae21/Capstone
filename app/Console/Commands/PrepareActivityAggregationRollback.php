<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PrepareActivityAggregationRollback extends Command
{
    protected $signature = 'activities:prepare-aggregation-rollback
        {--max-copies=10000 : Maximum legacy rows to create in this run}
        {--batch=500 : Rows inserted per transaction}';

    protected $description = 'Boundedly expand activity counters before rolling back aggregation columns';

    public function handle(): int
    {
        $maximum = max(1, min(100000, (int) $this->option('max-copies')));
        $batchSize = max(1, min(1000, (int) $this->option('batch')));
        $created = 0;

        while ($created < $maximum) {
            $copied = DB::transaction(function () use ($maximum, $batchSize, &$created): int {
                $activityQuery = DB::table('user_activities')
                    ->where('activity_count', '>', 1)
                    ->orderBy('activity_id');
                $activity = DB::connection()->getDriverName() === 'pgsql'
                    ? $activityQuery->lock('for update skip locked')->first()
                    : $activityQuery->lockForUpdate()->first();

                if (! $activity) {
                    return 0;
                }

                $count = min(
                    (int) $activity->activity_count - 1,
                    $batchSize,
                    $maximum - $created,
                );
                $rows = array_fill(0, $count, [
                    'user_id' => $activity->user_id,
                    'product_id' => $activity->product_id,
                    'activity_type' => $activity->activity_type,
                    'activity_window' => null,
                    'activity_count' => 1,
                    'created_at' => $activity->created_at,
                    'updated_at' => $activity->updated_at,
                ]);

                DB::table('user_activities')->insert($rows);
                DB::table('user_activities')->where('activity_id', $activity->activity_id)
                    ->decrement('activity_count', $count);

                return $count;
            }, 3);

            if ($copied === 0) {
                break;
            }

            $created += $copied;
        }

        $remaining = DB::table('user_activities')->where('activity_count', '>', 1)->exists();
        $this->info("Created {$created} legacy activity row(s).");

        if ($remaining) {
            $this->warn('Aggregated rows remain. Run this command again before rollback.');

            return self::FAILURE;
        }

        $this->info('Aggregation rollback is prepared; every activity row now has a count of one.');

        return self::SUCCESS;
    }
}
