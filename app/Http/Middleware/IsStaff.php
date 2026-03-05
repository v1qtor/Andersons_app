<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsStaff
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->role || $request->user()->role->name !== 'Staff') {
            abort(403, __('Unauthorized. Staff access only.'));
        }

        return $next($request);
    }
}
