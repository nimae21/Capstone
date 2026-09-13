<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AccountEmail;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $maxAttempts = 5;

    protected $decayMinutes = 1;

    /**
     * Where to redirect users after login.
     *
     * @return string
     */
    protected function redirectTo()
    {
        if (auth()->user()->role === 'super_admin') {
            return '/governance/approvals';
        }
        if (auth()->user()->role === 'admin') {
            return '/admin/dashboard';
        }

        return '/home';
    }

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    protected function throttleKey(Request $request)
    {
        return hash('sha256', strtolower(trim((string) $request->email)));
    }

    protected function hasTooManyLoginAttempts(Request $request)
    {
        return $this->limiter()->tooManyAttempts($this->throttleKey($request), $this->maxAttempts())
            || $this->limiter()->tooManyAttempts($this->ipThrottleKey($request), 30);
    }

    protected function incrementLoginAttempts(Request $request)
    {
        $this->limiter()->hit($this->throttleKey($request), $this->decayMinutes() * 60);
        $this->limiter()->hit($this->ipThrottleKey($request), $this->decayMinutes() * 60);
    }

    protected function sendLockoutResponse(Request $request)
    {
        $seconds = max(
            $this->limiter()->availableIn($this->throttleKey($request)),
            $this->limiter()->availableIn($this->ipThrottleKey($request)),
        );

        throw ValidationException::withMessages([
            $this->username() => [trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ])],
        ])->status(429);
    }

    private function ipThrottleKey(Request $request): string
    {
        return 'login-ip:'.$request->ip();
    }

    protected function credentials(Request $request)
    {
        return [
            'email' => AccountEmail::storedAddress((string) $request->email),
            'password' => $request->password,
            'is_active' => true,
        ];
    }
}
