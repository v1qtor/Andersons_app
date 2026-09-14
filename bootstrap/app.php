<?php

use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\IsChef;
use App\Http\Middleware\IsStaff;
use App\Http\Middleware\IsTheAndersons;
use App\Http\Middleware\IsUnavailabilityUser;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*', headers: Request::HEADER_X_FORWARDED_FOR |
            Request::HEADER_X_FORWARDED_HOST |
            Request::HEADER_X_FORWARDED_PORT |
            Request::HEADER_X_FORWARDED_PROTO
        );

        $middleware->alias([
            'admin' => IsAdmin::class,
            'chef' => IsChef::class,
            'staff' => IsStaff::class,
            'the-andersons' => IsTheAndersons::class,
            'unavailability' => IsUnavailabilityUser::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
