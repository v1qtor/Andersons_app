<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsUnavailabilityUser
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->role || ! in_array($request->user()->role->name, ['Staff', 'Chef', 'Admin'])) {
            abort(403, __('Unauthorized.'));
        }
        return $next($request);
    }
}
