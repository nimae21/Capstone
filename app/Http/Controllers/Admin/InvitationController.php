<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Auth\PendingRegistrationController;
use App\Http\Controllers\Controller;
use App\Models\AdminInvitation;
use App\Models\PendingRegistration;
use App\Models\User;
use App\Notifications\AccountLinkNotification;
use App\Services\AccountEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class InvitationController extends Controller
{
    public function create()
    {
        return view('admin.governance.invite');
    }

    public function store(Request $request)
    {
        $request->merge(['email' => Str::lower(trim((string) $request->email))]);
        $data = $request->validate(['email' => AccountEmail::newAccountRules()]);
        $token = Str::random(64);
        DB::transaction(function () use ($data, $token, $request) {
            abort_if(PendingRegistration::where('email', $data['email'])->where('expires_at', '>', now())->exists(), 409, 'This email has a pending customer registration. Wait for it to expire or use another email.');
            AdminInvitation::updateOrCreate(['email' => $data['email']], ['inviter_id' => $request->user()->id,
                'token_hash' => hash('sha256', $token), 'expires_at' => now()->addHours(48), 'accepted_at' => null, 'accepted_user_id' => null]);
        });
        try {
            Notification::route('mail', $data['email'])->notify(new AccountLinkNotification(route('invitation.show', $token), true));
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors(['email' => 'Email delivery failed. Please resend the invitation.']);
        }

        return back()->with('success', 'Invitation sent. The recipient chooses their own password. Resending invalidates the previous link.');
    }

    private function invitation(string $token, bool $lock = false): AdminInvitation
    {
        $query = AdminInvitation::where('token_hash', hash('sha256', $token));
        if ($lock) {
            $query->lockForUpdate();
        }
        $invitation = $query->first();
        abort_unless($invitation && ! $invitation->accepted_at && $invitation->expires_at->isFuture(), 410, 'This invitation has expired or was already used.');
        abort_unless(User::whereKey($invitation->inviter_id)->where('role', 'super_admin')->where('is_active', true)->exists(), 403, 'This invitation is no longer authorized.');

        return $invitation;
    }

    public function show(string $token)
    {
        return view('auth.invitation', ['invitation' => $this->invitation($token), 'token' => $token]);
    }

    public function accept(Request $request, string $token)
    {
        DB::transaction(function () use ($request, $token) {
            $invitation = $this->invitation($token, true);
            $request->merge(['email' => $invitation->email]);
            $data = $request->validate(PendingRegistrationController::rules());
            unset($data['terms']);
            $data['password'] = Hash::make($data['password']);
            $user = new User($data);
            $user->forceFill(['role' => 'admin', 'is_active' => true, 'email_verified_at' => now(), 'terms_accepted_at' => now()])->save();
            $invitation->update(['accepted_at' => now(), 'accepted_user_id' => $user->id]);
        });

        return redirect()->route('login')->with('status', 'Admin account created. Please log in with your new password.');
    }
}
