<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AdminInvitation;
use App\Models\PendingRegistration;
use App\Models\User;
use App\Notifications\AccountLinkNotification;
use App\Services\AccountEmail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PendingRegistrationController extends Controller
{
    public static function rules(): array
    {
        return ['first_name' => 'required|string|max:255', 'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255', 'suffix' => 'nullable|string|max:255',
            'email' => AccountEmail::newAccountRules(), 'password' => 'required|string|min:8|confirmed', 'terms' => 'required|accepted'];
    }

    public function store(Request $request)
    {
        $request->merge(['email' => Str::lower(trim((string) $request->email))]);
        $data = $request->validate(self::rules());
        if (AdminInvitation::where('email', $data['email'])->whereNull('accepted_at')->where('expires_at', '>', now())->exists()) {
            throw ValidationException::withMessages(['email' => 'Please use your admin invitation to finish account setup.']);
        }
        $data['password'] = Hash::make($data['password']);
        unset($data['terms']);
        $data['terms_accepted_at'] = now()->toDateTimeString();
        $token = Str::random(64);
        PendingRegistration::upsert([['email' => $data['email'], 'payload' => encrypt(json_encode($data, JSON_THROW_ON_ERROR), false),
            'token_hash' => hash('sha256', $token), 'expires_at' => now()->addHour(), 'created_at' => now(), 'updated_at' => now()]],
            ['email'], ['payload', 'token_hash', 'expires_at', 'updated_at']);
        try {
            Notification::route('mail', $data['email'])->notify(new AccountLinkNotification(route('registration.verify', $token)));
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors(['email' => 'We could not send the verification email. Please try registering again shortly.'])->withInput($request->except('password', 'password_confirmation'));
        }

        return redirect()->route('login')->with('status', 'Check your email. Your account will be created only after verification. The link expires in 60 minutes.');
    }

    public function verify(string $token)
    {
        DB::transaction(function () use ($token) {
            $pending = PendingRegistration::where('token_hash', hash('sha256', $token))->lockForUpdate()->first();
            abort_unless($pending && $pending->expires_at->isFuture(), 410, 'This verification link has expired or was already used. Please register again.');
            abort_if(AccountEmail::exists($pending->email), 409, 'This email is already registered. Please log in.');
            abort_if(AdminInvitation::where('email', $pending->email)->whereNull('accepted_at')->where('expires_at', '>', now())->exists(), 409, 'Use your admin invitation to finish setup.');
            $user = new User($pending->payload);
            $user->forceFill(['email' => $pending->email, 'role' => 'user', 'is_active' => true, 'email_verified_at' => now()])->save();
            $pending->delete();
            event(new Registered($user));
        });

        return redirect()->route('login')->with('status', 'Email verified. Your account is ready. Please log in.');
    }
}
