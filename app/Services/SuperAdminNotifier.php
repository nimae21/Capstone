<?php

namespace App\Services;

use App\Models\AdminInvitation;
use App\Models\ApprovalRequest;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\SuperAdminAlert;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;

/**
 * Single entry point for "something happened that the Super Admin should know
 * about". Fans the event out to every active Super Admin account as a database
 * notification (the in-app centre) and to their registered phones as a push.
 */
class SuperAdminNotifier
{
    public function __construct(protected MobilePushOutbox $outbox) {}

    public function alert(string $type, string $title, string $body, ?string $route = null, array $meta = []): int
    {
        $users = User::where('role', 'super_admin')->where('is_active', true)->get();

        if ($users->isEmpty()) {
            return 0;
        }

        $payload = [
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'route' => $route,
            'meta' => $meta,
        ];

        $now = now();
        $notificationIds = [];

        foreach ($users as $user) {
            $id = (string) Str::uuid();
            DatabaseNotification::create([
                'id' => $id,
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

        try {
            $this->outbox->alert($payload, $notificationIds);
        } catch (\Throwable $e) {
            // The in-app notification is the source of truth. A push provider
            // outage must never fail the business action that triggered it.
            report($e);
        }

        return count($notificationIds);
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
        );
    }

    public function inventoryAlert(string $level, string $product, string $variant, int $remaining): void
    {
        $this->alert(
            $level === 'out' ? 'inventory_out_of_stock' : 'inventory_low_stock',
            $level === 'out' ? 'Variant out of stock' : 'Low stock alert',
            $product.' - '.$variant.' now has '.$remaining.' unit(s) left.',
            '/tabs/inventory',
            ['product' => $product, 'variant' => $variant, 'remaining' => $remaining],
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
        );
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
