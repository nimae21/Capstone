<?php

namespace App\Console\Commands;

use App\Services\FirebasePushSender;
use App\Services\MobilePushOutbox;
use Illuminate\Console\Command;

class SendMobilePush extends Command
{
    protected $signature = 'mobile:send-push';
    protected $description = 'Deliver pending admin phone alerts with retries';

    public function handle(MobilePushOutbox $outbox, FirebasePushSender $sender): int
    {
        if (!$sender->configured()) {
            $this->warn('Mobile push is not configured.');
            return self::SUCCESS;
        }
        $this->info('Sent '.$outbox->deliverPending($sender).' mobile alerts.');
        return self::SUCCESS;
    }
}
