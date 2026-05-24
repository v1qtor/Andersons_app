<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schedule;
use Laravel\Fortify\Fortify;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::loginView(fn () => view('livewire.auth.login'));

        RateLimiter::for('login', function ($request) {
            return Limit::perMinute(5)->by($request->email.$request->ip());
        });
        // Load broadcast channels
        require base_path('routes/channels.php');

        // Schedule notification commands
        Schedule::command('trips:send-reminders')->daily();
        Schedule::command('checkpoints:check-arrivals')->everyThirtyMinutes();
        Schedule::command('trips:check-overdue')->hourly();
    }
}
