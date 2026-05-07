<?php

namespace App\Http\Controllers\Api;

use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class NotificationController extends Controller
{
    /**
     * Get all notifications for the authenticated user
     */
    public function index(Request $request)
    {
        $notifications = UserNotification::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(fn ($n) => [
                'id' => $n->id,
                'title' => $n->title,
                'message' => $n->message,
                'type' => $n->type,
                'action_url' => $n->action_url,
                'is_read' => $n->is_read,
                'created_at' => $n->created_at,
            ]);

        return response()->json([
            'notifications' => $notifications,
        ]);
    }
}
