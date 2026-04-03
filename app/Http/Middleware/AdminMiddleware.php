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
        // Check if user is logged in AND role is admin
    if (! $request->user() || $request->user()->role !== 'admin') {
        abort(403, 'Unauthorized'); // blocks access
    }


        return $next($request);
    }
}
