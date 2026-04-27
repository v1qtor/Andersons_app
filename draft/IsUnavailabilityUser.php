<?php

namespace draft;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsUnavailabilityUser
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->role || ! in_array($request->user()->role->name, ['Staff', 'Chef', 'Admin'])) {
            abort(403, __('Unauthorized.'));
        }

        return $next($request);
    }
}
