<?php

use Illuminate\Support\Facades\Broadcast;

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

// Private per-user notifications channel — subscribed to client-side as
// Echo.private(`user.${userId}`) in resources/js/app.js.
Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
