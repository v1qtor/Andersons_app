<style>
    @media (max-width: 1024px) {
        .notification-panel-desktop {
            height: 40vh !important;
            bottom: auto !important;
            overflow-y: auto !important;
        }
    }
    @media (min-width: 1025px) {
        .notification-panel-desktop {
            height: 100vh !important;
            bottom: 0 !important;
        }
    }
</style>

<div class="relative" x-data="desktopNotificationBell()" x-init="init()">
    <!-- Desktop Notification Bell -->
    <div class="py-2 px-4">
        <button
            @click="togglePanel()"
            class="relative w-full text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200 transition-colors rounded-lg hover:bg-gray-100 dark:hover:bg-neutral-700 flex items-center gap-3 py-2"
            title="Notifications"
        >
            <!-- Bell Icon -->
            <div class="relative flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                
                <!-- Counter Badge -->
                <template x-if="unreadCount > 0">
                    <span
                        :aria-label="unreadCount + ' unread notifications'"
                        class="absolute -top-2 -right-2 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white bg-red-600 rounded-full"
                        x-text="unreadCount > 99 ? '99+' : unreadCount"
                    ></span>
                </template>
            </div>

            <!-- Label -->
            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Notifications</span>
        </button>
    </div>

    <!-- Right-Side Panel Overlay -->
    <template x-if="isPanelOpen">
        <div
            @click="isPanelOpen = false"
            class="fixed inset-0 z-40 bg-black/50"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        ></div>
    </template>

    <!-- Right-Side Notification Panel -->
    <div
        class="notification-panel-desktop fixed right-0 top-0 w-96 max-w-full bg-white dark:bg-neutral-900 shadow-2xl z-50 flex flex-col border-l border-gray-200 dark:border-neutral-700"
        x-show="isPanelOpen"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        style="height: 40vh; overflow-y: auto;"
    >
        <!-- Panel Header -->
        <div class="bg-gray-50 dark:bg-neutral-800 border-b border-gray-200 dark:border-neutral-700 px-6 py-4 flex items-center justify-between flex-shrink-0">
            <h3 class="font-bold text-gray-900 dark:text-white text-lg">Notifications</h3>
            <button
                @click="isPanelOpen = false"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Notifications List -->
        <div class="flex-1 overflow-y-auto">
            <template x-if="notifications.length === 0">
                <div class="px-6 py-16 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">No notifications yet</p>
                </div>
            </template>

            <template x-for="notification in notifications" :key="notification.id">
                <div
                    class="px-6 py-4 border-b border-gray-100 dark:border-neutral-800 hover:bg-gray-50 dark:hover:bg-neutral-800/50 transition-colors"
                    :class="{ 'bg-indigo-50 dark:bg-indigo-900/20': !notification.is_read }"
                >
                    <div class="flex items-start gap-3">
                        <!-- Unread Indicator -->
                        <template x-if="!notification.is_read">
                            <div class="flex-shrink-0 pt-1.5">
                                <svg class="w-2 h-2 text-green-600" fill="currentColor" viewBox="0 0 8 8">
                                    <circle cx="4" cy="4" r="4" />
                                </svg>
                            </div>
                        </template>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <h4 class="font-semibold text-gray-900 dark:text-white text-sm" x-text="notification.title"></h4>
                            <p class="text-gray-600 dark:text-gray-400 text-sm mt-1" x-text="notification.message"></p>
                            <p class="text-gray-500 dark:text-gray-500 text-xs mt-2" x-text="formatTime(notification.created_at)"></p>
                            
                            <!-- Action Link -->
                            <template x-if="notification.action_url">
                                <a :href="notification.action_url" class="inline-block text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 mt-2 transition-colors">
                                    View Details →
                                </a>
                            </template>
                        </div>

                        <!-- Delete Button -->
                        <button
                            @click="deleteNotification(notification.id)"
                            class="mt-1 text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors flex-shrink-0"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <!-- Panel Footer -->
        <template x-if="notifications.length > 0">
            <div class="bg-gray-50 dark:bg-neutral-800 border-t border-gray-200 dark:border-neutral-700 px-6 py-3 flex-shrink-0">
                <button
                    @click="clearAll()"
                    class="w-full text-center text-sm font-semibold text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 transition-colors"
                >
                    Clear All
                </button>
            </div>
        </template>
    </div>
</div>

