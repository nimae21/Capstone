<?php

namespace App\Console\Commands;

use App\Services\OrderService;
use Illuminate\Console\Command;

class RecoverCheckoutRetries extends Command
{
    protected $signature = 'checkout-retries:recover {--limit=50}';

    protected $description = 'Resume durable PayMongo checkout retry operations';

    public function handle(OrderService $orders): int
    {
        $count = $orders->recoverPendingCheckoutRetries((int) $this->option('limit'));
        $this->info("Processed {$count} checkout retry operation(s).");

        return self::SUCCESS;
    }
}
