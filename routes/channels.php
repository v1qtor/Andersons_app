<?php

use Illuminate\Support\Facades\Broadcast;

Log::info('📡 Loading routes/channels.php');

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The required channels are declared here so that
| they may be registered by the application. Channels may be wildcard
| expressions or explicit channel names. The return value is the user
| instance.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Try matching with the "private-" prefix
Broadcast::channel('private-user.{id}', function ($user, $id) {
    Log::info('🔐 Matched private-user.{id}', ['user_id' => $user?->id, 'id' => $id]);

    return (bool) $user;
});

// Also try without prefix
Broadcast::channel('user.{id}', function ($user, $id) {
    Log::info('🔐 Matched user.{id}', ['user_id' => $user?->id, 'id' => $id]);

    return (bool) $user;
});

// Allow private user notifications channel
Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
