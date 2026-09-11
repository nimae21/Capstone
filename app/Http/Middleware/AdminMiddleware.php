<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $readOnly = $user?->role === 'super_admin' && $request->isMethod('GET')
            && ! $request->is('api/*') && ! $request->is('admin/*/edit') && ! $request->is('admin/pos*');
        if (! $user || ! $user->is_active || (! $user->isAdmin() && ! $readOnly)) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}
