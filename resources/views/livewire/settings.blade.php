<div class="w-full max-w-6xl mx-auto">
    <!-- Success Message Toast -->
    @if (session('status') || session('message'))
        <script>
            setTimeout(() => {
                const toast = document.getElementById('success-toast');
                if (toast) {
                    toast.style.animation = 'slideOut 0.3s ease-out forwards';
                }
            }, 3000);
        </script>
        <div id="success-toast" class="fixed top-4 right-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg shadow-lg p-4 max-w-md z-50" style="animation: slideIn 0.3s ease-out;">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <p class="text-green-800 dark:text-green-300 font-medium">{{ session('status') ?? session('message') }}</p>
            </div>
        </div>
        <style>
            @keyframes slideIn {
                from {
                    transform: translateX(400px);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(400px);
                    opacity: 0;
                }
            }
        </style>
    @endif

    <div class="space-y-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-2">Settings</h1>
            <p class="text-gray-600 dark:text-gray-400">Manage your personal information and notification preferences</p>
        </div>

        <!-- Personal Information Section -->
        <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-gray-200 dark:border-neutral-700 p-8">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Personal Information</h2>

            <form wire:submit="updatePersonalInfo" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Full Name -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Full Name</label>
                        <input
                            wire:model="name"
                            type="text"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                        >
                        @error('name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Email Address -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Email Address</label>
                        <input
                            wire:model="email"
                            type="email"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                        >
                        @error('email') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Phone Number -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Phone Number</label>
                        <input
                            wire:model="phone_number"
                            type="tel"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                        >
                    </div>

                    <!-- Bank Country -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Bank Country</label>
                        <select
                            wire:model="bankCountry"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                        >
                            <option value="United Kingdom">United Kingdom</option>
                            <option value="United States">United States</option>
                            <option value="Germany">Germany</option>
                            <option value="France">France</option>
                            <option value="Spain">Spain</option>
                            <option value="Italy">Italy</option>
                        </select>
                    </div>
                </div>

                <!-- IBAN -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">IBAN</label>
                    <input
                        wire:model="iban"
                        type="text"
                        placeholder="GB29 NWBK 6016 1331 9268 19"
                        class="w-full px-4 py-3 border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                    >
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">This will be automatically used when submitting invoices</p>
                </div>

                <!-- Save Button -->
                <div class="flex">
                    <button
                        type="submit"
                        class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-semibold"
                    >
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

        <!-- Notification Preferences Section -->
        <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-gray-200 dark:border-neutral-700 p-8">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Notification Preferences</h2>

            <div class="space-y-4">
                <!-- Header Row -->
                <div class="flex justify-between items-center pb-4 border-b border-gray-200 dark:border-neutral-700">
                    <div class="text-sm font-semibold text-gray-600 dark:text-gray-400"></div>
                    <div class="flex gap-16">
                        <div class="text-sm font-semibold text-gray-600 dark:text-gray-400 text-center w-20">Email</div>
                        <div class="text-sm font-semibold text-gray-600 dark:text-gray-400 text-center w-20">Pop-up</div>
                    </div>
                </div>

                <!-- Trip Delay Alerts -->
                <div class="flex items-center justify-between py-4">
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Trip Delay Alerts</p>
                    </div>
                    <div class="flex gap-16">
                        <div class="flex justify-center w-20">
                            <input
                                type="checkbox"
                                @checked($notifications['tripDelayAlerts']['email'])
                                wire:change="toggleNotification('tripDelayAlerts', 'email')"
                                class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                            >
                        </div>
                        <div class="flex justify-center w-20">
                            <input
                                type="checkbox"
                                @checked($notifications['tripDelayAlerts']['popup'])
                                wire:change="toggleNotification('tripDelayAlerts', 'popup')"
                                class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                            >
                        </div>
                    </div>
                </div>

                <!-- Task Reminders -->
                <div class="flex items-center justify-between py-4 border-t border-gray-100 dark:border-neutral-700">
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Task Reminders</p>
                    </div>
                    <div class="flex gap-16">
                        <div class="flex justify-center w-20">
                            <input
                                type="checkbox"
                                @checked($notifications['taskReminders']['email'])
                                wire:change="toggleNotification('taskReminders', 'email')"
                                class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                            >
                        </div>
                        <div class="flex justify-center w-20">
                            <input
                                type="checkbox"
                                @checked($notifications['taskReminders']['popup'])
                                wire:change="toggleNotification('taskReminders', 'popup')"
                                class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                            >
                        </div>
                    </div>
                </div>

                <!-- Receipt Approvals -->
                <div class="flex items-center justify-between py-4 border-t border-gray-100 dark:border-neutral-700">
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Receipt Approvals</p>
                    </div>
                    <div class="flex gap-16">
                        <div class="flex justify-center w-20">
                            <input
                                type="checkbox"
                                @checked($notifications['receiptApprovals']['email'])
                                wire:change="toggleNotification('receiptApprovals', 'email')"
                                class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                            >
                        </div>
                        <div class="flex justify-center w-20">
                            <input
                                type="checkbox"
                                @checked($notifications['receiptApprovals']['popup'])
                                wire:change="toggleNotification('receiptApprovals', 'popup')"
                                class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                            >
                        </div>
                    </div>
                </div>

                <!-- Dinner Signup Confirmations -->
                <div class="flex items-center justify-between py-4 border-t border-gray-100 dark:border-neutral-700">
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Dinner Signup Confirmations</p>
                    </div>
                    <div class="flex gap-16">
                        <div class="flex justify-center w-20">
                            <input
                                type="checkbox"
                                @checked($notifications['dinnerSignups']['email'])
                                wire:change="toggleNotification('dinnerSignups', 'email')"
                                class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                            >
                        </div>
                        <div class="flex justify-center w-20">
                            <input
                                type="checkbox"
                                @checked($notifications['dinnerSignups']['popup'])
                                wire:change="toggleNotification('dinnerSignups', 'popup')"
                                class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                            >
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
