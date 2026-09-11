<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\Order;
use Illuminate\Support\Str;

/**
 * One serialization of an order for every mobile endpoint, so the dashboard
 * preview and the order list can never disagree about what an order looks
 * like. Read-only: the Super Admin cannot change order state from the phone.
 */
class OrderPresenter
{
    public static function summary(Order $order): array
    {
        return [
            'id' => $order->order_id,
            'customer' => $order->user?->full_name ?? $order->full_name ?? 'Customer',
            'customer_email' => $order->user?->email,
            'recipient' => $order->full_name,
            'phone' => $order->phone_number,
            'total' => $order->total_amount,
            'created_at' => $order->created_at?->toIso8601String(),
            'status' => $order->status->label(),
            'status_key' => $order->status->value,
            'payment_method' => $order->payment_method,
            'items_count' => $order->relationLoaded('items') ? $order->items->sum('quantity') : null,
            'address' => implode(', ', array_filter([$order->street, $order->barangay, $order->city, $order->province, $order->postal_code])),
        ];
    }

    public static function detail(Order $order): array
    {
        $order->loadMissing(['user', 'items.variant.product', 'payment']);

        return array_merge(self::summary($order), [
            'items' => $order->items->map(fn ($item) => [
                'product' => $item->variant?->product?->product_name ?? 'Unavailable product',
                'product_id' => $item->variant?->product?->product_id,
                'size' => $item->variant?->size,
                'color' => $item->variant?->color,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'subtotal' => round((float) $item->price * (int) $item->quantity, 2),
            ]),
            'shipping' => [
                'recipient' => $order->full_name,
                'phone' => $order->phone_number,
                'street' => $order->street,
                'barangay' => $order->barangay,
                'city' => $order->city,
                'province' => $order->province,
                'postal_code' => $order->postal_code,
                'latitude' => $order->latitude,
                'longitude' => $order->longitude,
                'map_url' => $order->latitude && $order->longitude
                    ? 'https://www.google.com/maps/search/?api=1&query='.$order->latitude.','.$order->longitude
                    : null,
            ],
            'payment' => self::payment($order),
            'timeline' => self::timeline($order),
            // Fleet installations may still render the old shape.
            'address' => implode(', ', array_filter([$order->street, $order->barangay, $order->city, $order->province, $order->postal_code])),
        ]);
    }

    public static function payment(Order $order): ?array
    {
        $payment = $order->payment;
        if (! $payment) {
            return null;
        }

        return [
            'status' => $payment->status,
            'method' => $payment->method,
            'reference' => $payment->paymongo_payment_id,
            'paid_at' => $payment->payment_date?->toIso8601String(),
            'refund_status' => $payment->refund_status,
            'refund_label' => $payment->refund_label,
            'refund_amount' => $payment->refund_amount !== null ? round($payment->refund_amount / 100, 2) : null,
        ];
    }

    /**
     * The order's real history, rebuilt from the audit trail the website
     * already writes plus the payment record. No separate mobile-only log.
     */
    public static function timeline(Order $order): array
    {
        $entries = [];

        foreach (ActivityLog::with('user')
            ->where('subject_type', Order::class)
            ->where('subject_id', $order->order_id)
            ->orderBy('activity_log_id')
            ->get() as $log) {
            $changes = $log->changes ?? [];
            $entries[] = [
                'label' => self::logLabel($log->event, $changes),
                'at' => $log->created_at?->toIso8601String(),
                'by' => $log->user?->full_name,
                'status' => is_array($changes['status'] ?? null) ? ($changes['status']['new'] ?? null) : null,
                'kind' => 'log',
            ];
        }

        if ($payment = $order->payment) {
            if ($payment->status === 'completed' && $payment->payment_date) {
                $entries[] = [
                    'label' => 'Payment confirmed ('.$payment->method.')',
                    'at' => $payment->payment_date->toIso8601String(),
                    'by' => null,
                    'status' => 'paid',
                    'kind' => 'payment',
                ];
            }
            if ($payment->refund_status) {
                $entries[] = [
                    'label' => $payment->refund_label ?? 'Refund '.$payment->refund_status,
                    'at' => $payment->refund_requested_at?->toIso8601String(),
                    'by' => null,
                    'status' => 'cancelled',
                    'kind' => 'refund',
                ];
            }
        }

        usort($entries, fn ($a, $b) => strcmp((string) $a['at'], (string) $b['at']));

        return $entries;
    }

    private static function logLabel(string $event, array $changes): string
    {
        if (is_array($changes['status'] ?? null) && isset($changes['status']['new'])) {
            return 'Status changed to '.Str::headline((string) $changes['status']['new']);
        }

        return match ($event) {
            'created' => 'Order placed',
            'deleted' => 'Order removed',
            default => 'Order '.$event,
        };
    }
}