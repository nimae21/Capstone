<?php

namespace App\Observers;

use App\Enums\OrderStatus;
use App\Enums\SaleType;
use App\Models\Order;
use App\Services\SuperAdminNotifier;

/**
 * Turns the existing order lifecycle into Super Admin alerts. The observer is
 * the single hook, so every path that changes an order (website checkout,
 * PayMongo webhook, admin panel) produces the same notification - the phone
 * never has to guess what happened.
 *
 * Alert copy deliberately carries no customer name or address: this payload is
 * also pushed to lock screens.
 */
class MobileOrderObserver
{
    public function __construct(protected SuperAdminNotifier $notifier) {}

    public function created(Order $order): void
    {
        if ($order->sale_type !== SaleType::Online) {
            return;
        }

        $this->notifier->alert(
            'order_placed',
            'New order placed',
            'Order #'.$order->order_id.' - '.$this->peso($order).' placed.',
            '/tabs/orders/'.$order->order_id,
            ['order_id' => $order->order_id, 'status' => 'pending'],
        );
    }

    public function updated(Order $order): void
    {
        if ($order->sale_type !== SaleType::Online || ! $order->wasChanged('status')) {
            return;
        }

        $status = $order->status;
        $type = match ($status) {
            OrderStatus::Paid => 'order_paid',
            OrderStatus::Shipped => 'order_shipped',
            OrderStatus::Completed => 'order_completed',
            OrderStatus::Cancelled => 'order_cancelled',
            default => 'order_updated',
        };

        $this->notifier->alert(
            $type,
            $this->title($status, $order),
            'Order #'.$order->order_id.' - '.$this->peso($order).'.',
            '/tabs/orders/'.$order->order_id,
            ['order_id' => $order->order_id, 'status' => $status->value],
        );
    }

    private function title(OrderStatus $status, Order $order): string
    {
        return match ($status) {
            OrderStatus::Paid => 'Order #'.$order->order_id.' paid',
            OrderStatus::Shipped => 'Order #'.$order->order_id.' shipped',
            OrderStatus::Completed => 'Order #'.$order->order_id.' completed',
            OrderStatus::Cancelled => 'Order #'.$order->order_id.' cancelled',
            default => 'Order #'.$order->order_id.' updated',
        };
    }

    private function peso(Order $order): string
    {
        return 'PHP '.number_format((float) $order->total_amount, 2);
    }
}