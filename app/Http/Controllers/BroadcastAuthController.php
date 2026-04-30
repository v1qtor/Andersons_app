<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BroadcastAuthController extends Controller
{
    public function auth(Request $request)
    {
        \Log::info('🔐 Broadcast Auth Request', [
            'channel_name' => $request->input('channel_name'),
            'user_id' => $request->user()?->id,
            'is_authenticated' => $request->user() ? 'yes' : 'no',
        ]);

        // Get the channel name
        $channel = $request->input('channel_name');
        $user = $request->user();

        // Check if the channel can be accessed
        if ($user) {
            // For private-user.{id} channels, extract the ID and compare with user ID
            if (preg_match('/^private-user\.(\d+)$/', $channel, $matches)) {
                $userId = (int) $matches[1];
                $authenticated = (int) $user->id === $userId;
                
                \Log::info('🔐 Private user channel auth', [
                    'channel' => $channel,
                    'user_id' => $user->id,
                    'channel_user_id' => $userId,
                    'authenticated' => $authenticated,
                ]);

                if ($authenticated) {
                    // Use Laravel's Broadcast facade to generate the auth response
                    return Broadcast::auth($request);
                }
            }
        }

        \Log::warning('🔐 Broadcast auth failed', [
            'channel' => $channel,
            'user_id' => $user?->id,
        ]);

        return response()->json(['error' => 'Unauthorized'], 403);
    }
}
