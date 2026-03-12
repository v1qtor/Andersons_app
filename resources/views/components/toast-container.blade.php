<div x-data="toastContainer()" x-init="init()" class="fixed bottom-6 right-6 z-50 space-y-3 pointer-events-none">
    <template x-for="notification in notifications" :key="notification.id">
        <div
            x-show="notification.visible"
            :aria-label="notification.title"
            class="pointer-events-auto bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-lg shadow-lg p-4 max-w-sm animate-in slide-in-from-right"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="translate-x-96 opacity-0"
            x-transition:enter-end="translate-x-0 opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="translate-x-0 opacity-100"
            x-transition:leave-end="translate-x-96 opacity-0"
        >
            <div class="flex items-start gap-3">
                <!-- Icon -->
                <div class="flex-shrink-0 pt-1">
                    <template x-if="notification.type === 'success' || notification.type === 'task_assigned'">
                        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </template>
                    <template x-if="notification.type === 'error'">
                        <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </template>
                    <template x-if="notification.type === 'info' || notification.type === 'collaboration_request'">
                        <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </template>
                </div>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-gray-900 dark:text-white text-sm" x-text="notification.title"></h3>
                    <template x-if="notification.message">
                        <p class="text-gray-600 dark:text-gray-400 text-sm mt-1" x-text="notification.message"></p>
                    </template>
                </div>

                <!-- Close Button -->
                <button
                    @click="removeNotification(notification.id)"
                    class="flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Progress Bar -->
            <div
                class="absolute bottom-0 left-0 h-1 bg-gray-300 dark:bg-neutral-600 rounded-b-lg"
                :style="`width: ${notification.progress}%`"
            ></div>
        </div>
    </template>
</div>

<script>
    function toastContainer() {
        return {
            notifications: [],
            nextId: 0,

            addToast(title, message = '', type = 'info') {
                const id = this.nextId++;
                const notification = {
                    id,
                    title,
                    message,
                    type,
                    visible: true,
                    progress: 100,
                };

                this.notifications.push(notification);

                // Auto-dismiss after 4 seconds with progress animation
                const duration = 4000;
                const startTime = Date.now();
                const interval = setInterval(() => {
                    const elapsed = Date.now() - startTime;
                    const progress = Math.max(0, 100 - (elapsed / duration) * 100);

                    const notif = this.notifications.find(n => n.id === id);
                    if (notif) {
                        notif.progress = progress;
                    }

                    if (elapsed >= duration) {
                        clearInterval(interval);
                        this.removeNotification(id);
                    }
                }, 50);
            },

            removeNotification(id) {
                const notification = this.notifications.find(n => n.id === id);
                if (notification) {
                    notification.visible = false;
                    // Remove from array after animation completes
                    setTimeout(() => {
                        this.notifications = this.notifications.filter(n => n.id !== id);
                    }, 200);
                }
            },

            init() {
                // Make toast functions globally available
                window.showToast = (title, message, type = 'info') => {
                    this.addToast(title, message, type);
                };
            }
        }
    }
</script>
