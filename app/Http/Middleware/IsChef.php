<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsChef
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->role || $request->user()->role->name !== 'Chef') {
            abort(403, __('Unauthorized. Chef access only.'));
        }

        return $next($request);
    }
}
