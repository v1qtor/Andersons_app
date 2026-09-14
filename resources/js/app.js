import './echo-config';

// Configure toast notifications
window.showToast = function (title, message, type = 'info') {
    console.log(`🔔 Toast: ${type} - ${title}: ${message}`);
};

// Track processed notifications to prevent duplicates from Echo delivery
window.processedNotificationIds = new Set();

// Define global notification bell functions for Alpine.js
window.notificationBell = function () {
    return {
        isPanelOpen: false,
        notifications: [],
        unreadCount: 0,

        async loadNotifications() {
            try {
                const response = await fetch('/api/notifications', {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json',
                    },
                });
                if (response.ok) {
                    const data = await response.json();
                    this.notifications = data.notifications || [];
                    this.unreadCount = this.notifications.filter((n) => !n.is_read).length;
                }
            } catch (error) {
                console.error('Error loading notifications:', error);
            }
        },

        togglePanel() {
            this.isPanelOpen = !this.isPanelOpen;
            if (this.isPanelOpen) {
                this.loadNotifications();
            }
        },

        addNotification(notification) {
            this.notifications.unshift(notification);
            this.unreadCount = this.notifications.filter((n) => !n.is_read).length;
        },

        deleteNotification(id) {
            this.notifications = this.notifications.filter((n) => n.id !== id);
            this.unreadCount = this.notifications.filter((n) => !n.is_read).length;

            fetch(`/notifications/${id}`, {
                method: 'DELETE',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                },
            }).catch((error) => console.error('Error deleting notification:', error));
        },

        async clearAll() {
            this.notifications = [];
            this.unreadCount = 0;

            try {
                // Note: route() is not available in JS, using hardcoded path for now
                await fetch('/notifications/clear-all', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
            } catch (error) {
                console.error('Error clearing notifications:', error);
            }
        },

        formatTime(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diff = now - date;

            const seconds = Math.floor(diff / 1000);
            const minutes = Math.floor(diff / 60000);
            const hours = Math.floor(diff / 3600000);
            const days = Math.floor(diff / 86400000);

            if (seconds < 60) return 'just now';
            if (minutes < 60) return `${minutes}m ago`;
            if (hours < 24) return `${hours}h ago`;
            if (days < 7) return `${days}d ago`;

            return date.toLocaleDateString();
        },

        init() {
            this.loadNotifications();
        },
    };
};

// Define desktop notification bell version
window.desktopNotificationBell = function () {
    return {
        isPanelOpen: false,
        notifications: [],
        unreadCount: 0,

        async loadNotifications() {
            try {
                const response = await fetch('/api/notifications', {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json',
                    },
                });

                if (!response.ok) {
                    return;
                }

                const data = await response.json();
                this.notifications = data.notifications || [];
                this.unreadCount = this.notifications.filter((n) => !n.is_read).length;
            } catch (error) {
                console.error('Error loading notifications:', error);
            }
        },

        async clearAll() {
            this.notifications = [];
            this.unreadCount = 0;

            try {
                await fetch('/notifications/clear-all', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
            } catch (error) {
                console.error('Error clearing notifications:', error);
            }
        },

        togglePanel() {
            this.isPanelOpen = !this.isPanelOpen;
            if (this.isPanelOpen) {
                this.loadNotifications();
            }
        },

        addNotification(notification) {
            this.notifications.unshift(notification);
            this.unreadCount = this.notifications.filter((n) => !n.is_read).length;
        },

        deleteNotification(id) {
            this.notifications = this.notifications.filter((n) => n.id !== id);
            this.unreadCount = this.notifications.filter((n) => !n.is_read).length;

            fetch(`/notifications/${id}`, {
                method: 'DELETE',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                },
            }).catch((error) => console.error('Error deleting notification:', error));
        },

        formatTime(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diff = now - date;

            const seconds = Math.floor(diff / 1000);
            const minutes = Math.floor(diff / 60000);
            const hours = Math.floor(diff / 3600000);
            const days = Math.floor(diff / 86400000);

            if (seconds < 60) return 'just now';
            if (minutes < 60) return `${minutes}m ago`;
            if (hours < 24) return `${hours}h ago`;
            if (days < 7) return `${days}d ago`;

            return date.toLocaleDateString();
        },

        init() {
            this.$nextTick(() => {
                this.loadNotifications();
            });

            window.desktopNotificationBellInstance = this;
            if (!window.desktopNotificationBellPoller) {
                window.desktopNotificationBellPoller = setInterval(() => {
                    window.desktopNotificationBellInstance?.loadNotifications();
                }, 30000);
            }

            // Listen for real-time notifications - ONLY HERE to avoid duplication
            if (window.Echo) {
                const userId =
                    document.querySelector('body')?.getAttribute('data-user-id') ||
                    (window.Laravel && window.Laravel.userId);

                if (userId) {
                    window.Echo.private(`user.${userId}`).listen('NotificationCreated', (data) => {
                        // Prevent processing the same notification twice (Echo sometimes delivers duplicates)
                        if (window.processedNotificationIds.has(data.notification.id)) {
                            return;
                        }

                        window.processedNotificationIds.add(data.notification.id);

                        this.addNotification(data.notification);
                        // Trigger toast notification
                        if (window.showToast) {
                            window.showToast(
                                data.notification.title,
                                data.notification.message,
                                data.notification.type || 'info'
                            );
                        }
                    });
                }
            }
        },
    };
};
