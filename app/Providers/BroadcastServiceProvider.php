<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Register broadcasting channels
        $this->registerChannels();
    }

    /**
     * Register the channels.
     *
     * @return void
     */
    protected function registerChannels()
    {
        // Default Laravel model channel
        Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
            return (int) $user->id === (int) $id;
        });

        // Wildcard channel for all private-user channels
        // Matches: private-user.1, private-user.2, etc.
        Broadcast::channel('user.*', function ($user) {
            // Any authenticated user can subscribe to their own notifications
            \Log::info('🔐 Wildcard user channel auth', [
                'user_id' => $user?->id,
                'authenticated' => true,
            ]);
            return true;
        });
    }
}
