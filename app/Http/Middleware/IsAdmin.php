<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->role || $request->user()->role->name !== 'System Administrator') {
            abort(403, __('Unauthorized. Admin access only.'));
        }

        return $next($request);
    }
}

