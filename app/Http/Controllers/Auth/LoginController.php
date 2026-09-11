<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AccountEmail;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

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
        return hash('sha256', strtolower(trim((string) $request->email)).'|'.$request->ip());
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
