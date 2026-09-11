<?php

namespace App\Observers;

use App\Models\ApprovalRequest;
use App\Services\SuperAdminNotifier;

class ApprovalRequestObserver
{
    public function created(ApprovalRequest $request): void
    {
        app(SuperAdminNotifier::class)->approvalSubmitted($request);
    }
}