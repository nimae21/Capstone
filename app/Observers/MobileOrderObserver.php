<?php

namespace App\Observers;

use App\Enums\OrderStatus;
use App\Enums\SaleType;
use App\Models\Order;
use App\Services\MobilePushOutbox;

class MobileOrderObserver
{
    public function updated(Order $order): void
    {
        if (config('mobile_push.enabled') && $order->wasChanged('status')
            && $order->status === OrderStatus::Paid && $order->sale_type === SaleType::Online) {
            // Insert into the same DB transaction as payment confirmation.
            // The delivery command cannot see the rows until that transaction commits.
            app(MobilePushOutbox::class)->orderPaid($order);
        }
    }
}
