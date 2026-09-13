<?php

namespace App\Console\Commands;

use App\Jobs\ProcessAdminAlert;
use App\Jobs\RecordUserActivities;
use App\Models\BackgroundOperation;
use App\Services\ReliableJobDispatcher;
use Illuminate\Console\Command;
use Throwable;

class ReconcileBackgroundOperations extends Command
{
    protected $signature = 'background:reconcile';

    protected $description = 'Redispatch durable activity and admin-alert operations missed by the queue';

    public function handle(ReliableJobDispatcher $dispatcher): int
    {
        $operations = BackgroundOperation::whereNull('processed_at')
            ->where('available_at', '<=', now())
            ->whereIn('type', ['user_activity', 'admin_alert', 'inventory_check'])
            ->orderBy('id')
            ->limit((int) config('background_jobs.reconcile_batch', 100))
            ->get();

        $dispatched = 0;
        foreach ($operations as $operation) {
            try {
                if ($operation->type === 'user_activity') {
                    $payload = $operation->payload;
                    $queued = $dispatcher->dispatch(new RecordUserActivities(
                        (int) $payload['user_id'],
                        (array) $payload['product_ids'],
                        (string) $payload['activity_type'],
                        (string) $payload['occurred_at'],
                        $operation->operation_key,
                    ));
                } else {
                    $queued = $dispatcher->dispatch(new ProcessAdminAlert($operation->operation_key));
                }
            } catch (Throwable $exception) {
                report($exception);
                $operation->update([
                    'available_at' => now()->addMinutes(5),
                    'last_error' => substr($exception->getMessage(), 0, 255),
                ]);

                continue;
            }

            if ($queued) {
                $dispatched++;
            }
        }

        $this->info("Redispatched {$dispatched} pending background operation(s).");

        return self::SUCCESS;
    }
}
