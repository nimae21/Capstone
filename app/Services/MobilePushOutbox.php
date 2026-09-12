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
    /**
     * Kinds where one summary beats a burst of separate buzzes. Only alerts
     * that have not been sent yet are folded together, so this can never
     * rewrite a notification the Super Admin has already received.
     */
    private const DIGESTIBLE = ['approval_submitted', 'order_placed'];

    /** How long a burst of the same kind keeps collecting in one alert. */
    private const DIGEST_WINDOW_SECONDS = 60;

    /**
     * Queue the paid-order alert for every registered Super Admin phone.
     * Kept as its own method because payment confirmation calls it directly.
     */
    public function orderPaid(Order $order): void
    {
        $this->alert([
            'type' => 'order_paid',
            'title' => 'New paid order',
            'body' => "Order #{$order->order_id} is ready to review.",
            'route' => "/tabs/orders/{$order->order_id}",
            'meta' => ['order_id' => $order->order_id],
        ], [], 'order-paid:'.$order->order_id, $order->order_id);
    }

    /**
     * Fan a notification out to every eligible Super Admin device.
     *
     * @param  array  $payload  the stored notification payload (title/body/route/meta)
     * @param  array  $notificationIds  in-app notification id keyed by user id
     */
    public function alert(array $payload, array $notificationIds = [], ?string $eventKey = null, ?int $orderId = null): void
    {
        if (! config('mobile_push.enabled')) {
            return;
        }

        $kind = substr((string) ($payload['type'] ?? 'alert'), 0, 30);
        $orderId = $orderId ?? (isset($payload['meta']['order_id']) ? (int) $payload['meta']['order_id'] : null);
        // Order alerts are keyed by order, so a retried webhook or a replayed
        // status change cannot notify the same phone twice.
        $eventKey = $eventKey ?? ($orderId
            ? $kind.':'.$orderId
            : $kind.':'.substr(hash('sha256', json_encode($payload)), 0, 48));

        MobilePushDevice::eligible()->each(function (MobilePushDevice $device) use ($payload, $notificationIds, $eventKey, $orderId, $kind) {
            if ($this->foldIntoDigest($device, $kind)) {
                return;
            }

            MobilePushDelivery::firstOrCreate([
                'device_id' => $device->id,
                'personal_access_token_id' => $device->personal_access_token_id,
                'event_key' => $eventKey,
            ], [
                'kind' => $kind,
                'order_id' => $orderId,
                'notification_id' => $notificationIds[$device->user_id] ?? null,
                'data' => $payload,
                'available_at' => now(),
            ]);
        });
    }

    /**
     * An admin who submits a batch of changes - or a burst of orders landing
     * together - should not send the Super Admin one buzz per row. When an
     * alert of the same kind is still waiting to be sent, it is rewritten as a
     * summary ("4 changes awaiting approval") instead of queueing another.
     */
    private function foldIntoDigest(MobilePushDevice $device, string $kind): bool
    {
        if (! in_array($kind, self::DIGESTIBLE, true)) {
            return false;
        }

        $waiting = MobilePushDelivery::where('device_id', $device->id)
            ->where('personal_access_token_id', $device->personal_access_token_id)
            ->where('kind', $kind)
            ->where('status', 'pending')
            // A delivery that is being sent right now already carries its data.
            ->whereNull('locked_at')
            ->where('created_at', '>=', now()->subSeconds(self::DIGEST_WINDOW_SECONDS))
            ->latest('id')
            ->first();

        if (! $waiting) {
            return false;
        }

        $count = (int) ($waiting->data['meta']['digest_count'] ?? 1) + 1;
        $waiting->update([
            'data' => $this->digestPayload($kind, $count),
            // A summary is not tied to one record, so tapping it opens the
            // queue. The individual notifications stay unread in the app.
            'notification_id' => null,
            'available_at' => now(),
        ]);

        return true;
    }

    private function digestPayload(string $kind, int $count): array
    {
        return match ($kind) {
            'approval_submitted' => [
                'type' => $kind,
                'title' => $count.' changes awaiting approval',
                'body' => 'Admin submissions are queued for your review.',
                'route' => '/tabs/approvals',
                'meta' => ['digest_count' => $count],
            ],
            default => [
                'type' => $kind,
                'title' => $count.' new orders placed',
                'body' => 'New online orders came in. Open the orders queue to review them.',
                'route' => '/tabs/orders',
                'meta' => ['digest_count' => $count],
            ],
        };
    }

    public function deliverPending(FirebasePushSender $sender): int
    {
        if (! $sender->configured()) {
            return 0;
        }
        Cache::put('mobile-push-worker-last-seen', now()->timestamp, now()->addMinutes(5));
        $ids = MobilePushDelivery::where('status', 'pending')->where('available_at', '<=', now())
            ->where(fn ($q) => $q->whereNull('locked_at')->orWhere('locked_at', '<', now()->subMinutes(5)))
            ->orderBy('id')->limit(25)->pluck('id');
        $sent = 0;
        foreach ($ids as $id) {
            // Atomic claim also protects against overlapping command invocations.
            $claimed = MobilePushDelivery::whereKey($id)->where('status', 'pending')
                ->where(fn ($q) => $q->whereNull('locked_at')->orWhere('locked_at', '<', now()->subMinutes(5)))
                ->update(['locked_at' => now()]);
            if (! $claimed) {
                continue;
            }
            $delivery = MobilePushDelivery::find($id);
            if (! $delivery) {
                continue; // Session may have been revoked after claiming.
            }
            $device = MobilePushDevice::eligible()->whereKey($delivery->device_id)
                ->where('personal_access_token_id', $delivery->personal_access_token_id)->first();
            // A paid-order alert is pointless once the website already handled it.
            $stillRelevant = $delivery->kind !== 'order_paid'
                || ($delivery->order_id && Order::whereKey($delivery->order_id)->where('status', OrderStatus::Paid)->exists());
            if (! $device || ! $stillRelevant || $delivery->created_at->lt(now()->subDay())) {
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
                $retryable = ! $error instanceof PushSendException || $error->retryable;
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
