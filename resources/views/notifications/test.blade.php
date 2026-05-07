<x-layouts.app>
    <div class="w-full max-w-4xl mx-auto">
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-2">Notification System Test</h1>
            <p class="text-gray-600 dark:text-gray-400">Test the real-time notification system with these buttons. Check the notification bell icon in the sidebar and watch for toast messages.</p>
        </div>

        <!-- Information Card -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-6 mb-8">
            <p class="text-sm text-blue-800 dark:text-blue-300">
                💡 <strong>Instructions:</strong> Click any button below to send a test notification. You'll see the notification count increase in the bell icon (top-right sidebar) and a toast popup will appear. Check the notification panel to see notification details.
            </p>
        </div>

        <!-- Test Buttons -->
        <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-gray-200 dark:border-neutral-700 p-8">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Test Scenarios</h2>

            <div class="space-y-4">
                <!-- Simple Test Notification -->
                <div class="flex items-start gap-4 p-4 bg-gray-50 dark:bg-neutral-700 rounded-lg">
                    <div class="flex-1">
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Basic Test Notification</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Send a simple info notification to test the system basics.</p>
                    </div>
                    <button 
                        onclick="sendTestNotification()"
                        class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition-colors whitespace-nowrap"
                    >
                        Send
                    </button>
                </div>

                <!-- Task Assigned -->
                <div class="flex items-start gap-4 p-4 bg-gray-50 dark:bg-neutral-700 rounded-lg">
                    <div class="flex-1">
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Task Assignment Notification</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Simulate a task being assigned to you (staff receive this when admin assigns tasks).</p>
                    </div>
                    <button 
                        onclick="sendTaskAssignmentNotification()"
                        class="px-6 py-2 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-lg transition-colors whitespace-nowrap"
                    >
                        Send
                    </button>
                </div>

                <!-- Collaboration Request -->
                <div class="flex items-start gap-4 p-4 bg-gray-50 dark:bg-neutral-700 rounded-lg">
                    <div class="flex-1">
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Collaboration Request</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Simulate a collaboration request from another staff member.</p>
                    </div>
                    <button 
                        onclick="sendCollaborationRequestNotification()"
                        class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg transition-colors whitespace-nowrap"
                    >
                        Send
                    </button>
                </div>

                <!-- Clear All -->
                <div class="flex items-start gap-4 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                    <div class="flex-1">
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Clear All Notifications</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Remove all notifications from the system.</p>
                    </div>
                    <button 
                        onclick="clearAllNotifications()"
                        class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition-colors whitespace-nowrap"
                    >
                        Clear All
                    </button>
                </div>
            </div>
        </div>

        <!-- Statistics Card -->
        <div class="mt-8 bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-gray-200 dark:border-neutral-700 p-8">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">What to Expect</h2>

            <div class="grid md:grid-cols-3 gap-4">
                <div class="text-center p-4">
                    <div class="text-3xl font-bold text-indigo-600 mb-2">1</div>
                    <p class="text-sm text-gray-600 dark:text-gray-400"><strong>Bell Counter Updates</strong><br/>The notification count in the sidebar bell icon will increase in real-time.</p>
                </div>
                <div class="text-center p-4">
                    <div class="text-3xl font-bold text-indigo-600 mb-2">2</div>
                    <p class="text-sm text-gray-600 dark:text-gray-400"><strong>Toast Appears</strong><br/>A popup notification will appear at the top/bottom of your screen.</p>
                </div>
                <div class="text-center p-4">
                    <div class="text-3xl font-bold text-indigo-600 mb-2">3</div>
                    <p class="text-sm text-gray-600 dark:text-gray-400"><strong>Sidebar Panel</strong><br/>Click the bell icon to see all your notifications in a panel.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // CSRF token from meta tag
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        async function sendTestNotification() {
            console.log('🔵 Sending test notification...');
            try {
                console.log('📡 POST to:', '{{ route("notifications.send-test") }}');
                const response = await fetch('{{ route("notifications.send-test") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                    },
                });
                
                console.log('✅ Response status:', response.status);
                const data = await response.json();
                
                if (data.success) {
                    console.log('✅ Notification sent:', data.notification);
                    // Show as toast notification instead of alert
                    if (window.showToast) {
                        window.showToast(data.notification.title, data.notification.message, 'success');
                    }
                } else {
                    console.error('❌ API response not successful:', data);
                    if (window.showToast) {
                        window.showToast('Error', data.error || 'Unknown error', 'error');
                    }
                }
            } catch (error) {
                console.error('❌ Error sending notification:', error);
                if (window.showToast) {
                    window.showToast('Error', error.message, 'error');
                }
            }
        }

        async function sendTaskAssignmentNotification() {
            console.log('🟡 Sending task assignment notification...');
            try {
                const response = await fetch('{{ route("notifications.send-task-assignment") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                    },
                });
                const data = await response.json();
                
                if (data.success) {
                    console.log('✅ Task notification sent:', data.notification);
                    if (window.showToast) {
                        window.showToast(data.notification.title, data.notification.message, 'success');
                    }
                } else {
                    console.error('❌ Failed:', data);
                    if (window.showToast) {
                        window.showToast('Error', data.error || 'Unknown error', 'error');
                    }
                }
            } catch (error) {
                console.error('❌ Error:', error);
                if (window.showToast) {
                    window.showToast('Error', error.message, 'error');
                }
            }
        }

        async function sendCollaborationRequestNotification() {
            console.log('🟢 Sending collaboration notification...');
            try {
                const response = await fetch('{{ route("notifications.send-collaboration-request") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                    },
                });
                const data = await response.json();
                
                if (data.success) {
                    console.log('✅ Collaboration notification sent:', data.notification);
                    if (window.showToast) {
                        window.showToast(data.notification.title, data.notification.message, 'success');
                    }
                } else {
                    console.error('❌ Failed:', data);
                    if (window.showToast) {
                        window.showToast('Error', data.error || 'Unknown error', 'error');
                    }
                }
            } catch (error) {
                console.error('❌ Error:', error);
                if (window.showToast) {
                    window.showToast('Error', error.message, 'error');
                }
            }
        }

        async function clearAllNotifications() {
            console.log('🔴 Clearing all notifications...');
            if (!confirm('Are you sure you want to clear all notifications?')) {
                console.log('   Cancelled');
                return;
            }

            try {
                const response = await fetch('{{ route("notifications.clear-all") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                    },
                });
                const data = await response.json();
                if (data.success) {
                    console.log('✅ All notifications cleared');
                    if (window.showToast) {
                        window.showToast('Success', 'All notifications cleared!', 'success');
                    }
                } else {
                    console.error('❌ Failed:', data);
                    if (window.showToast) {
                        window.showToast('Error', data.error || 'Unknown error', 'error');
                    }
                }
            } catch (error) {
                console.error('❌ Error clearing notifications:', error);
                if (window.showToast) {
                    window.showToast('Error', error.message, 'error');
                }
            }
        }
    </script>
</x-layouts.app>
