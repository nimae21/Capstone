<?php

namespace App\Services;

use App\Models\AdminInvitation;
use App\Models\PendingRegistration;
use App\Models\User;
use App\Notifications\AccountLinkNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

/**
 * The one place an Admin invitation is created. Both the website controller
 * and the mobile API call this, so the token, expiry, and single-use rules
 * cannot drift apart between the two clients.
 */
class AdminInvitationService
{
    public function invite(User $inviter, string $email): AdminInvitation
    {
        $email = Str::lower(trim($email));
        $token = Str::random(64);

        $invitation = DB::transaction(function () use ($email, $token, $inviter) {
            abort_if(
                PendingRegistration::where('email', $email)->where('expires_at', '>', now())->exists(),
                409,
                'This email has a pending customer registration. Wait for it to expire or use another email.'
            );

            return AdminInvitation::updateOrCreate(['email' => $email], [
                'inviter_id' => $inviter->id,
                'token_hash' => hash('sha256', $token),
                'expires_at' => now()->addHours(48),
                'accepted_at' => null,
                'accepted_user_id' => null,
            ]);
        });

        // Deliberately outside the transaction: a mail provider failure must
        // not roll back the invitation row, and the caller decides the message.
        Notification::route('mail', $email)->notify(new AccountLinkNotification(route('invitation.show', $token), true));

        return $invitation;
    }

    /** Pending (not yet accepted, not expired) invitations, newest first. */
    public function pending()
    {
        return AdminInvitation::with(['inviter', 'acceptedUser'])
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->orderByDesc('id');
    }
}