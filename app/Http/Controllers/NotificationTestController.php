<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;

class NotificationTestController extends Controller
{
    /**
     * Show the notification test page
     */
    public function index()
    {
        return view('notifications.test');
    }

    /**
     * Send a test notification to the current user
     */
    public function sendTestNotification()
    {
        $user = Auth::user();

        \Illuminate\Support\Facades\Log::info('📝 Creating test notification for user', ['user_id' => $user->id]);

        $notification = UserNotification::create([
            'user_id' => $user->id,
            'from_user_id' => $user->id,
            'title' => 'Test Notification',
            'message' => 'This is a test notification sent at ' . now()->format('H:i:s'),
            'type' => 'info',
        ]);

        \Illuminate\Support\Facades\Log::info('✅ Notification created', ['notification_id' => $notification->id]);

        // Broadcast the notification synchronously (QUEUE_CONNECTION=sync)
        \Illuminate\Support\Facades\Log::info('📡 Broadcasting notification event', ['channel' => 'user.' . $user->id]);
        broadcast(new \App\Events\NotificationCreated($notification));

        \Illuminate\Support\Facades\Log::info('✅ Broadcast sent');

        return response()->json(['success' => true, 'notification' => $notification]);
    }

    /**
     * Send a task assignment notification
     */
    public function sendTaskAssignmentNotification()
    {
        $user = Auth::user();

        \Illuminate\Support\Facades\Log::info('📝 Creating task assignment notification for user', ['user_id' => $user->id]);

        $notification = UserNotification::create([
            'user_id' => $user->id,
            'from_user_id' => $user->id,
            'title' => 'Task Assigned',
            'message' => $user->name . ' assigned you a new task',
            'type' => 'task_assigned',
            'action_url' => '/schedule',
        ]);

        \Illuminate\Support\Facades\Log::info('✅ Task notification created', ['notification_id' => $notification->id]);
        \Illuminate\Support\Facades\Log::info('📡 Broadcasting task assignment event');
        
        broadcast(new \App\Events\NotificationCreated($notification));

        \Illuminate\Support\Facades\Log::info('✅ Task broadcast sent');

        return response()->json(['success' => true, 'notification' => $notification]);
    }

    /**
     * Send a collaboration request notification
     */
    public function sendCollaborationRequestNotification()
    {
        $user = Auth::user();

        \Illuminate\Support\Facades\Log::info('📝 Creating collaboration request notification for user', ['user_id' => $user->id]);

        $notification = UserNotification::create([
            'user_id' => $user->id,
            'from_user_id' => $user->id,
            'title' => 'Collaboration Request',
            'message' => $user->name . ' requested your collaboration on a task',
            'type' => 'collaboration_request',
            'action_url' => '/schedule',
        ]);

        \Illuminate\Support\Facades\Log::info('✅ Collaboration notification created', ['notification_id' => $notification->id]);
        \Illuminate\Support\Facades\Log::info('📡 Broadcasting collaboration request event');
        
        broadcast(new \App\Events\NotificationCreated($notification));

        \Illuminate\Support\Facades\Log::info('✅ Collaboration broadcast sent');

        return response()->json(['success' => true, 'notification' => $notification]);
    }

    /**
     * Clear all notifications
     */
    public function clearAll()
    {
        UserNotification::where('user_id', Auth::id())->delete();

        return response()->json(['success' => true, 'message' => 'All notifications cleared']);
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead($id)
    {
        $notification = UserNotification::findOrFail($id);

        if ($notification->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    /**
     * Delete a specific notification
     */
    public function deleteNotification($id)
    {
        $notification = UserNotification::findOrFail($id);

        if ($notification->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->delete();

        return response()->json(['success' => true]);
    }
}
