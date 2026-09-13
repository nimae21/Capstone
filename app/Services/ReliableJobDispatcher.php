<?php

namespace App\Services;

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Facades\DB;

class ReliableJobDispatcher
{
    public function dispatch(object $job): bool
    {
        $dispatch = function () use ($job): bool {
            try {
                app(Dispatcher::class)->dispatch($job);

                return true;
            } catch (\Throwable $exception) {
                report($exception);

                return false;
            }
        };

        if (DB::transactionLevel() > 0) {
            DB::afterCommit($dispatch);

            return true;
        }

        return $dispatch();
    }
}
