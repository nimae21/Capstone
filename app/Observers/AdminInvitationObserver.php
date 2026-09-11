<?php

namespace App\Observers;

use App\Models\AdminInvitation;
use App\Services\SuperAdminNotifier;

class AdminInvitationObserver
{
    public function updated(AdminInvitation $invitation): void
    {
        if (! $invitation->wasChanged('accepted_at') || ! $invitation->accepted_at) {
            return;
        }

        app(SuperAdminNotifier::class)->invitationAccepted($invitation);
    }
}