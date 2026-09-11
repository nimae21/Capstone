<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AdminInvitation;
use App\Models\ApprovalRequest;
use App\Models\User;
use App\Services\AccountEmail;
use App\Services\AdminInvitationService;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function invitations()
    {
        return response()->json(app(AdminInvitationService::class)->pending()->paginate(25)
            ->through(fn (AdminInvitation $invitation) => $this->presentInvitation($invitation)));
    }

    /**
     * Uses the website invitation workflow (AdminInvitationService) so tokens,
     * expiry and the email the recipient receives are identical.
     */
    public function invite(Request $request, AdminInvitationService $invitations)
    {
        $data = $request->validate(['email' => AccountEmail::newAccountRules()]);

        try {
            $invitation = $invitations->invite($request->user(), (string) $data['email']);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => 'The invitation could not be emailed. Please retry.'], 502);
        }

        $invitation->load('inviter');

        return response()->json([
            'message' => 'Invitation sent to '.$invitation->email.'. The recipient chooses their own password.',
            'invitation' => $this->presentInvitation($invitation),
        ], 201);
    }

    private function presentInvitation(AdminInvitation $invitation): array
    {
        return [
            'id' => $invitation->id,
            'email' => $invitation->email,
            'invited_by' => $invitation->inviter?->full_name,
            'expires_at' => $invitation->expires_at?->toIso8601String(),
            'accepted' => $invitation->accepted_at !== null,
            'accepted_at' => $invitation->accepted_at?->toIso8601String(),
            'created_at' => $invitation->created_at?->toIso8601String(),
        ];
    }

    public function show(Request $request, User $admin)
    {
        abort_unless($admin->role === 'admin', 404);

        $admin->loadCount('orders');

        $activity = ActivityLog::where('user_id', $admin->id)
            ->orderByDesc('activity_log_id')->take(10)->get()
            ->map(fn ($log) => [
                'id' => $log->activity_log_id,
                'action' => $log->action,
                'event' => $log->event,
                'subject' => $log->subject_label,
                'created_at' => $log->created_at?->toIso8601String(),
            ])->values();

        return response()->json([
            'id' => $admin->id,
            'name' => $admin->full_name,
            'email' => $admin->email,
            'initials' => $admin->initials,
            'role' => $admin->role,
            'role_label' => 'Admin',
            'is_active' => (bool) $admin->is_active,
            'status' => $admin->is_active ? 'Active' : 'Suspended',
            'email_verified' => $admin->email_verified_at !== null,
            'created_at' => $admin->created_at?->toIso8601String(),
            'approvals_submitted' => ApprovalRequest::where('requester_id', $admin->id)->count(),
            'approvals_approved' => ApprovalRequest::where('requester_id', $admin->id)->where('status', 'approved')->count(),
            'approvals_pending' => ApprovalRequest::where('requester_id', $admin->id)->where('status', 'pending')->count(),
            'invited_by' => AdminInvitation::where('accepted_user_id', $admin->id)->with('inviter')->first()?->inviter?->full_name,
            'can_suspend' => $admin->id !== $request->user()->id,
            'recent_activity' => $activity,
        ]);
    }
}