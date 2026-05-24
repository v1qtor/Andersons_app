@extends('layouts.app')

@section('content')
<div class="py-8 px-6 max-w-4xl mx-auto" style="background: #f8fbff;">
    <h1 class="text-4xl font-extrabold mb-2 text-blue-900">Notification System Mockup</h1>
    <p class="text-lg text-gray-600 mb-8">Here's how notifications will appear in your dashboard</p>

    <!-- Notification Bell UI -->
    <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
        <h2 class="text-2xl font-bold mb-6">Notification Bell (Top-Right Corner)</h2>
        <p class="text-gray-600 mb-6">Click the bell icon 🔔 in the top-right corner of your dashboard to open the notification panel:</p>
        
        <div class="bg-gray-50 p-6 rounded-lg border-2 border-gray-200">
            <div class="flex items-center gap-3 mb-6">
                <span class="text-4xl">🔔</span>
                <span class="text-xl font-bold text-red-600">3</span>
                <span class="text-gray-600">unread notifications</span>
            </div>
        </div>
    </div>

    <!-- Notification Examples -->
    <div class="bg-white rounded-2xl shadow-lg p-8">
        <h2 class="text-2xl font-bold mb-6">Example Notifications</h2>
        
        <!-- Info Notification -->
        <div class="mb-8 p-4 border-l-4 border-blue-500 bg-blue-50 rounded">
            <div class="flex items-start gap-4">
                <div class="text-2xl">ℹ️</div>
                <div class="flex-1">
                    <h3 class="font-bold text-lg text-blue-900">Trip Reminder: Yorkshire Moors Adventure</h3>
                    <p class="text-blue-700 mt-1">Your trip starts tomorrow! Don't forget to pack and confirm your arrival at checkpoints.</p>
                    <p class="text-xs text-blue-600 mt-2">May 25, 2026 • 2:30 PM</p>
                </div>
                <button class="text-blue-500 hover:text-blue-700 font-bold">✕</button>
            </div>
        </div>

        <!-- Warning Notification -->
        <div class="mb-8 p-4 border-l-4 border-yellow-500 bg-yellow-50 rounded">
            <div class="flex items-start gap-4">
                <div class="text-2xl">⚠️</div>
                <div class="flex-1">
                    <h3 class="font-bold text-lg text-yellow-900">Checkpoint Confirmation Needed</h3>
                    <p class="text-yellow-700 mt-1">Everyone has reached <strong>Malham Cove</strong> except you. Please confirm when you arrive.</p>
                    <p class="text-xs text-yellow-600 mt-2">May 24, 2026 • 4:15 PM</p>
                </div>
                <button class="text-yellow-500 hover:text-yellow-700 font-bold">✕</button>
            </div>
        </div>

        <!-- Error Notification -->
        <div class="mb-8 p-4 border-l-4 border-red-500 bg-red-50 rounded">
            <div class="flex items-start gap-4">
                <div class="text-2xl">🚨</div>
                <div class="flex-1">
                    <h3 class="font-bold text-lg text-red-900">Trip Overdue Alert</h3>
                    <p class="text-red-700 mt-1">The trip <strong>Lake District Trek</strong> has not returned by the expected time <strong>May 23, 6:00 PM</strong>. Please check if everyone is accounted for.</p>
                    <p class="text-xs text-red-600 mt-2">May 23, 2026 • 6:05 PM</p>
                </div>
                <button class="text-red-500 hover:text-red-700 font-bold">✕</button>
            </div>
        </div>

        <!-- Success Notification -->
        <div class="mb-8 p-4 border-l-4 border-green-500 bg-green-50 rounded">
            <div class="flex items-start gap-4">
                <div class="text-2xl">✅</div>
                <div class="flex-1">
                    <h3 class="font-bold text-lg text-green-900">Trip Accepted</h3>
                    <p class="text-green-700 mt-1">You have successfully accepted the trip <strong>Scottish Highlands Explorer</strong> with 2 plus-ones.</p>
                    <p class="text-xs text-green-600 mt-2">May 24, 2026 • 10:20 AM</p>
                </div>
                <button class="text-green-500 hover:text-green-700 font-bold">✕</button>
            </div>
        </div>
    </div>

    <!-- How They Work -->
    <div class="bg-white rounded-2xl shadow-lg p-8 mt-8">
        <h2 class="text-2xl font-bold mb-6">How Notifications Work</h2>
        
        <div class="space-y-6">
            <div class="flex gap-4">
                <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center font-bold text-blue-600">1</div>
                <div>
                    <h3 class="font-bold text-lg">Automatic Daily Reminders</h3>
                    <p class="text-gray-600 mt-1">Every day, you receive a reminder for trips starting tomorrow. The server runs this automatically at a scheduled time.</p>
                </div>
            </div>

            <div class="flex gap-4">
                <div class="flex-shrink-0 w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center font-bold text-yellow-600">2</div>
                <div>
                    <h3 class="font-bold text-lg">Checkpoint Alerts Every 30 Minutes</h3>
                    <p class="text-gray-600 mt-1">When someone hasn't confirmed a checkpoint after 2 hours, they receive a notification every 30 minutes asking them to confirm their arrival.</p>
                </div>
            </div>

            <div class="flex gap-4">
                <div class="flex-shrink-0 w-10 h-10 bg-red-100 rounded-full flex items-center justify-center font-bold text-red-600">3</div>
                <div>
                    <h3 class="font-bold text-lg">Overdue Trip Alerts Hourly</h3>
                    <p class="text-gray-600 mt-1">If a trip hasn't returned by its buffer alert time, the entire household is notified every hour.</p>
                </div>
            </div>

            <div class="flex gap-4">
                <div class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-full flex items-center justify-center font-bold text-green-600">4</div>
                <div>
                    <h3 class="font-bold text-lg">View in Dashboard</h3>
                    <p class="text-gray-600 mt-1">Click the bell icon at the top-right of any page to see all your notifications. Marked notifications appear in the dashboard.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Testing -->
    <div class="bg-indigo-50 rounded-2xl border-2 border-indigo-200 p-8 mt-8">
        <h2 class="text-2xl font-bold mb-4 text-indigo-900">Test Notifications</h2>
        <p class="text-indigo-800 mb-6">Visit the <strong><a href="{{ route('notifications.test') }}" class="underline">notifications test page</a></strong> to send test notifications and see how they appear in real-time.</p>
        <p class="text-indigo-700 text-sm">The notifications bell will update automatically when new notifications arrive.</p>
    </div>
</div>
@endsection
