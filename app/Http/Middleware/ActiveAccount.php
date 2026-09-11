<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ActiveAccount
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user() && ! $request->user()->is_active) {
            if ($request->hasSession()) {
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Your account is suspended. Contact the store for assistance.'], 403);
            }

            return redirect()->route('login')->withErrors(['email' => 'Your account is suspended. Contact the store for assistance.']);
        }

        return $next($request);
    }
}
