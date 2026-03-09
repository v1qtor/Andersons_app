<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsTheAndersons
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->role || $request->user()->role->name !== 'The Andersons') {
            abort(403, __('Unauthorized. The Andersons access only.'));
        }

        return $next($request);
    }
}
