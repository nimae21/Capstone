<?php

namespace App\Services;

use App\Jobs\ProcessAdminAlert;
use App\Models\AdminInvitation;
use App\Models\ApprovalRequest;
use App\Models\BackgroundOperation;
use App\Models\InventoryAlertState;
use App\Models\Payment;
use App\Models\ProductVariant;
use App\Models\User;
use App\Notifications\SuperAdminAlert;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

/**
 * Single entry point for "something happened that the Super Admin should know
 * about". Fans the event out to every active Super Admin account as a database
 * notification (the in-app centre) and to their registered phones as a push.
 */
class SuperAdminNotifier
{
    public function __construct(protected MobilePushOutbox $outbox) {}

    public function alert(
        string $type,
        string $title,
        string $body,
        ?string $route = null,
        array $meta = [],
        ?string $eventKey = null,
    ): int {
        $eventKey ??= $type.':'.Str::uuid();
        $payload = [
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'route' => $route,
            'meta' => $meta,
        ];
        $now = now();
        DB::table('background_operations')->insertOrIgnore([
            'operation_key' => $eventKey,
            'type' => 'admin_alert',
            'payload' => json_encode($payload, JSON_THROW_ON_ERROR),
            'available_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        app(ReliableJobDispatcher::class)->dispatch(new ProcessAdminAlert($eventKey));

        return 1;
    }

    public function queueInventoryCheck(int $variantId): void
    {
        $eventKey = 'inventory-check:'.Str::uuid();
        $now = now();
        DB::table('background_operations')->insert([
            'operation_key' => $eventKey,
            'type' => 'inventory_check',
            'payload' => json_encode(['variant_id' => $variantId], JSON_THROW_ON_ERROR),
            'available_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        app(ReliableJobDispatcher::class)->dispatch(new ProcessAdminAlert($eventKey));
    }

    public function materializeOperation(string $eventKey): bool
    {
        return DB::transaction(function () use ($eventKey): bool {
            $operation = BackgroundOperation::where('operation_key', $eventKey)
                ->lockForUpdate()->first();
            if (! $operation || $operation->processed_at) {
                return false;
            }

            $payload = $operation->payload;
            if ($operation->type === 'inventory_check') {
                $payload = $this->inventoryPayload((int) ($payload['variant_id'] ?? 0));
                if ($payload === null) {
                    $operation->update([
                        'attempts' => $operation->attempts + 1,
                        'processed_at' => now(),
                        'last_error' => null,
                    ]);

                    return false;
                }
            } elseif ($operation->type !== 'admin_alert') {
                throw new \RuntimeException("Unsupported background operation [{$operation->type}].");
            }

            $users = User::where('role', 'super_admin')->where('is_active', true)->get();
            $now = now();
            $notificationIds = [];

            foreach ($users as $user) {
                $id = Uuid::uuid5(
                    Uuid::NAMESPACE_URL,
                    'achilles-admin-alert:'.$eventKey.':'.$user->getKey(),
                )->toString();
                DatabaseNotification::firstOrCreate(['id' => $id], [
                    'type' => SuperAdminAlert::class,
                    'notifiable_type' => $user->getMorphClass(),
                    'notifiable_id' => $user->getKey(),
                    'data' => $payload,
                    'read_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $notificationIds[$user->getKey()] = $id;
            }

            $this->outbox->alert($payload, $notificationIds, $eventKey);
            $operation->update([
                'attempts' => $operation->attempts + 1,
                'processed_at' => now(),
                'last_error' => null,
            ]);

            return $users->isNotEmpty();
        });
    }

    public function approvalSubmitted(ApprovalRequest $request): void
    {
        $requester = $request->requester;
        $label = str_replace('_', ' ', (string) $request->entity_type);
        $name = $this->proposedName($request->payload ?? []);

        $this->alert(
            'approval_submitted',
            'Approval needed',
            trim(($requester?->full_name ?? 'An admin').' submitted '.$label.($name ? ' "'.$name.'"' : '').' for review.'),
            '/tabs/approvals/'.$request->getKey(),
            ['approval_id' => $request->getKey(), 'entity_type' => $request->entity_type],
            'approval-submitted:'.$request->getKey(),
        );
    }

    public function invitationAccepted(AdminInvitation $invitation): void
    {
        $admin = $invitation->acceptedUser;

        $this->alert(
            'invitation_accepted',
            'Admin invitation accepted',
            ($admin?->full_name ?? $invitation->email).' accepted the admin invitation.',
            '/tabs/users/admins',
            ['invitation_id' => $invitation->getKey(), 'email' => $invitation->email],
            'invitation-accepted:'.$invitation->getKey(),
        );
    }

    public function accountStatusChanged(User $account, bool $active, ?User $actor = null): void
    {
        $role = ucfirst(str_replace('_', ' ', (string) $account->role));
        $by = $actor && (int) $actor->getKey() !== (int) $account->getKey() ? ' by '.$actor->full_name : '';

        $this->alert(
            $active ? 'account_reactivated' : 'account_suspended',
            $active ? 'Account reactivated' : 'Account suspended',
            $account->full_name.' ('.$role.') was '.($active ? 'reactivated' : 'suspended').$by.'.',
            $account->role === 'admin' ? '/tabs/users/admins/'.$account->getKey() : '/tabs/users/'.$account->getKey(),
            ['user_id' => $account->getKey(), 'role' => $account->role, 'is_active' => $active],
            'account-status:'.$account->getKey().':'.($active ? 'active:' : 'inactive:')
                .($account->updated_at?->format('U.u') ?? now()->format('U.u')),
        );
    }

    public function securityAlert(string $title, string $body, array $meta = []): void
    {
        $this->alert('security_alert', $title, $body, '/tabs/logs', $meta);
    }

    /**
     * Money leaving the business is worth a phone alert, but only for the
     * outcomes that need a decision: a completed or failed refund. Intermediate
     * states stay in the payment record.
     */
    public function refundSynced(Payment $payment, string $status): void
    {
        $failed = $status === 'failed';
        $amount = number_format(((int) $payment->refund_amount) / 100, 2);

        $this->alert(
            $failed ? 'refund_failed' : 'refund_completed',
            $failed ? 'Refund failed' : 'Refund completed',
            $failed
                ? 'The refund for order #'.$payment->order_id.' could not be completed. Review the payment.'
                : 'Order #'.$payment->order_id.' was refunded (PHP '.$amount.').',
            '/tabs/orders/'.$payment->order_id,
            [
                'order_id' => (int) $payment->order_id,
                'refund_status' => $status,
                'refund_amount' => (int) $payment->refund_amount,
            ],
            'refund:'.$payment->getKey().':'.$status,
        );
    }

    private function inventoryPayload(int $variantId): ?array
    {
        $variant = ProductVariant::with('product')->find($variantId);
        if (! $variant || ! $variant->product) {
            return null;
        }

        $remaining = (int) $variant->stocks()->where('is_archived', false)->sum('remaining_quantity');
        if ($remaining > 5) {
            return null;
        }

        $level = $remaining <= 0 ? 'out' : 'low';
        $now = now();
        DB::table('inventory_alert_states')->insertOrIgnore([
            'product_variant_id' => $variantId,
            'level' => $level,
            'last_notified_at' => $now->copy()->subHours(13),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $state = InventoryAlertState::where('product_variant_id', $variantId)
            ->where('level', $level)->lockForUpdate()->firstOrFail();

        if ($state->last_notified_at->gt(now()->subHours(12))) {
            return null;
        }

        $state->update(['last_notified_at' => now()]);
        $variantLabel = 'Size '.$variant->size.' / '.$variant->color;

        return [
            'type' => $level === 'out' ? 'inventory_out_of_stock' : 'inventory_low_stock',
            'title' => $level === 'out' ? 'Variant out of stock' : 'Low stock alert',
            'body' => $variant->product->product_name.' - '.$variantLabel.' now has '.max(0, $remaining).' unit(s) left.',
            'route' => '/tabs/inventory',
            'meta' => [
                'product' => $variant->product->product_name,
                'variant' => $variantLabel,
                'remaining' => max(0, $remaining),
            ],
        ];
    }

    private function proposedName(array $payload): ?string
    {
        foreach (['product_name', 'category_name', 'brand_name', 'shoe_type_name'] as $key) {
            if (! empty($payload[$key]) && is_scalar($payload[$key])) {
                return (string) $payload[$key];
            }
        }

        return null;
    }
}
