<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Exceptions\PushSendException;
use App\Models\MobilePushDelivery;
use App\Models\MobilePushDevice;
use App\Models\Order;
use Illuminate\Support\Facades\Cache;

class MobilePushOutbox
{
    public function orderPaid(Order $order): void
    {
        MobilePushDevice::eligible()->each(function ($device) use ($order) {
            MobilePushDelivery::firstOrCreate([
                'device_id' => $device->id,
                'personal_access_token_id' => $device->personal_access_token_id,
                'event_key' => 'order-paid:'.$order->order_id,
            ], ['kind' => 'order_paid', 'order_id' => $order->order_id, 'available_at' => now()]);
        });
    }

    public function deliverPending(FirebasePushSender $sender): int
    {
        if (!$sender->configured()) return 0;
        Cache::put('mobile-push-worker-last-seen', now()->timestamp, now()->addMinutes(5));
        $ids = MobilePushDelivery::where('status', 'pending')->where('available_at', '<=', now())
            ->where(fn ($q) => $q->whereNull('locked_at')->orWhere('locked_at', '<', now()->subMinutes(5)))
            ->orderBy('id')->limit(10)->pluck('id');
        $sent = 0;
        foreach ($ids as $id) {
            // Atomic claim also protects against overlapping command invocations.
            $claimed = MobilePushDelivery::whereKey($id)->where('status', 'pending')
                ->where(fn ($q) => $q->whereNull('locked_at')->orWhere('locked_at', '<', now()->subMinutes(5)))
                ->update(['locked_at' => now()]);
            if (!$claimed) continue;
            $delivery = MobilePushDelivery::find($id);
            if (!$delivery) continue; // Session may have been revoked after claiming.
            $device = MobilePushDevice::eligible()->whereKey($delivery->device_id)
                ->where('personal_access_token_id', $delivery->personal_access_token_id)->first();
            $orderStillPending = $delivery->kind !== 'order_paid'
                || Order::whereKey($delivery->order_id)->where('status', OrderStatus::Paid)->exists();
            if (!$device || !$orderStillPending || $delivery->created_at->lt(now()->subDay())) {
                $delivery->update(['status' => 'skipped', 'locked_at' => null]);
                continue;
            }
            $delivery->increment('attempts');
            try {
                $result = $sender->send($device, $delivery);
                if ($result === 'unregistered') {
                    $device->update(['enabled' => false]);
                    $delivery->update(['status' => 'failed', 'last_error' => 'unregistered', 'locked_at' => null]);
                } else {
                    $delivery->update(['status' => 'sent', 'sent_at' => now(), 'locked_at' => null, 'last_error' => null]);
                    $sent++;
                }
            } catch (\Throwable $error) {
                $retryable = !$error instanceof PushSendException || $error->retryable;
                $delivery->update([
                    'status' => $retryable && $delivery->attempts < 5 ? 'pending' : 'failed',
                    'available_at' => now()->addSeconds(min(3600, 60 * (2 ** ($delivery->attempts - 1)))),
                    'last_error' => $error instanceof PushSendException ? $error->getMessage() : 'delivery_connection_failed',
                    'locked_at' => null,
                ]);
            }
            Cache::put('mobile-push-worker-last-seen', now()->timestamp, now()->addMinutes(5));
        }
        return $sent;
    }
}
