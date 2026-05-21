<div class="w-full max-w-6xl mx-auto"
    x-data="{
        showRemoveModal: false,
        showPasswordModal: false,
        removeItemId: null,
        removeItemName: '',
        removeMethod: '',
        removeModalTitle: '',
        removeContextLabel: '',
        openModal(id, name, method, title, context) {
            this.removeItemId = id;
            this.removeItemName = name;
            this.removeMethod = method;
            this.removeModalTitle = title;
            this.removeContextLabel = context;
            this.showRemoveModal = true;
        },
        confirm() {
            this.$wire[this.removeMethod](this.removeItemId);
            this.showRemoveModal = false;
        }
    }"
    x-effect="document.body.style.overflow = (showRemoveModal || showPasswordModal) ? 'hidden' : ''"
    @password-updated.window="showPasswordModal = false"
>

    <x-ui.flash-alert fixed="true" />

    <x-settings.confirm-remove-modal />

    <!-- Change Password Modal -->
    <div
        x-show="showPasswordModal"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        @keydown.escape.window="showPasswordModal = false"
        @mousedown.self="showPasswordModal = false"
        x-cloak
    >
        <div
            class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-8"
        >
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900">Change Password</h3>
                <button @click="showPasswordModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <form wire:submit="updatePassword" class="space-y-5" x-data="{
                showCurrent: false, showNew: false, showConfirm: false,
                cooldown: 0,
                cooldownTimer: null,
                startCooldown(s) {
                    this.cooldown = s;
                    clearInterval(this.cooldownTimer);
                    this.cooldownTimer = setInterval(() => { if (--this.cooldown <= 0) { this.cooldown = 0; clearInterval(this.cooldownTimer); } }, 1000);
                }
            }"
            @rate-limited.window="startCooldown($event.detail.seconds)">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Current Password</label>
                    <div class="relative">
                        <input
                            wire:model="currentPassword"
                            :type="showCurrent ? 'text' : 'password'"
                            class="w-full px-4 py-3 pr-11 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                            autocomplete="current-password"
                        >
                        <button type="button" @click="showCurrent = !showCurrent" class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600 transition-colors">
                            <svg x-show="!showCurrent" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="showCurrent" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.477 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </div>
                    @error('currentPassword') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">New Password</label>
                    <div class="relative">
                        <input
                            wire:model="newPassword"
                            :type="showNew ? 'text' : 'password'"
                            class="w-full px-4 py-3 pr-11 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                            autocomplete="new-password"
                        >
                        <button type="button" @click="showNew = !showNew" class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600 transition-colors">
                            <svg x-show="!showNew" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="showNew" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.477 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </div>
                    @error('newPassword') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Confirm New Password</label>
                    <div class="relative">
                        <input
                            wire:model="newPasswordConfirmation"
                            :type="showConfirm ? 'text' : 'password'"
                            class="w-full px-4 py-3 pr-11 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                            autocomplete="new-password"
                        >
                        <button type="button" @click="showConfirm = !showConfirm" class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600 transition-colors">
                            <svg x-show="!showConfirm" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="showConfirm" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.477 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button
                        type="submit"
                        :disabled="cooldown > 0"
                        :class="cooldown > 0 ? 'bg-indigo-300 cursor-not-allowed' : 'bg-indigo-600 hover:bg-indigo-700'"
                        class="flex-1 px-6 py-3 text-white rounded-lg transition-colors font-semibold"
                    >
                        Update Password
                    </button>
                    <button
                        type="button"
                        @click="showPasswordModal = false"
                        class="flex-1 px-6 py-3 bg-gray-100 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-200 transition-colors font-semibold"
                    >
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

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
                <!-- Row 1: Full Name & Email -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Full Name</label>
                        <input
                            wire:model="name"
                            type="text"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                        >
                        @error('name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Email Address</label>
                        <input
                            wire:model="email"
                            type="email"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                        >
                        @error('email') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Row 2: Phone Number & IBAN -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Phone Number</label>
                        <input
                            wire:model="phone_number"
                            type="tel"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                        >
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">IBAN</label>
                            <button type="button" wire:click="toggleShowIban" class="inline-flex items-center gap-1 px-3 py-1 rounded-md bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-200 dark:hover:bg-indigo-900/50 transition-colors font-medium text-xs">
                                @if($showIban)
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0114 19c5.523 0 10-4.477 10-10S19.523 -1 14 -1s-10 4.477-10 10a9.99 9.99 0 001.25 4.9M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    Hide
                                @else
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    Show
                                @endif
                            </button>
                        </div>
                        @if($showIban)
                            <input
                                wire:model="iban"
                                type="text"
                                placeholder="GB29 NWBK 6016 1331 9268 19"
                                class="w-full px-4 py-3 border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                            >
                        @else
                            <div class="w-full px-4 py-3 border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 rounded-lg bg-gray-50 dark:bg-neutral-700 flex items-center">
                                <p class="text-gray-600 dark:text-gray-400 font-mono text-sm">{{ $iban ? '•••• •••• •••• ' . substr($iban, -4) : 'No IBAN set' }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Row 3: Address & Date of Birth -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Address</label>
                        <textarea
                            wire:model="address"
                            rows="3"
                            placeholder="Enter your address"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                        ></textarea>
                        @error('address') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Date of Birth</label>
                        <input
                            type="date"
                            wire:model="birthdate"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                        />
                        @error('birthdate') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Row 4: Change Password Button -->
                <div>
                    <button
                        type="button"
                        @click="showPasswordModal = true"
                        class="w-full px-4 py-3 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 border border-indigo-300 dark:border-indigo-700 rounded-lg hover:bg-indigo-200 dark:hover:bg-indigo-900/50 transition-colors font-semibold text-sm flex items-center justify-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                        Change Password
                    </button>
                </div>

                <!-- Row 6: Save Button -->
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
            <p class="text-gray-600 dark:text-gray-400 mb-6">Choose which notifications you'd like to receive as pop-ups.</p>

            <div class="space-y-4">
                <!-- Trips -->
                <div class="flex items-center justify-between py-4 border-b border-gray-200 dark:border-neutral-700">
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Trips</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Get notified for trips (invites, alerts, reminders)</p>
                    </div>
                    <x-ui.toggle :enabled="$notifications['trips']" wire="toggleNotification('trips')" />
                </div>

                <!-- Task Assignments -->
                <div class="flex items-center justify-between py-4 border-b border-gray-200 dark:border-neutral-700">
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Task Assignments</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Get notified when assigned a new task</p>
                    </div>
                    <x-ui.toggle :enabled="$notifications['taskAssignments']" wire="toggleNotification('taskAssignments')" />
                </div>

                <!-- Collaboration Requests -->
                <div class="flex items-center justify-between py-4 border-b border-gray-200 dark:border-neutral-700">
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Collaboration Requests</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Get notified of collaboration opportunities</p>
                    </div>
                    <x-ui.toggle :enabled="$notifications['collaborationRequests']" wire="toggleNotification('collaborationRequests')" />
                </div>

                <!-- Receipt Approvals -->
                <div class="flex items-center justify-between py-4 border-b border-gray-200 dark:border-neutral-700">
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Receipt Approvals</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Get notified when your receipts are approved</p>
                    </div>
                    <x-ui.toggle :enabled="$notifications['receiptApprovals']" wire="toggleNotification('receiptApprovals')" />
                </div>

                <!-- Meal Notifications -->
                <div class="flex items-center justify-between py-4">
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Meal Notifications</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Get notified about meal invitations</p>
                    </div>
                    <x-ui.toggle :enabled="$notifications['mealNotifications']" wire="toggleNotification('mealNotifications')" />
                </div>
            </div>
        </div>

        <x-settings.tag-list-section
            title="Allergies"
            :items="$userAllergies"
            color="indigo"
            wire-model="newAllergy"
            add-action="addAllergy"
            placeholder="Enter allergy name"
            current-label="Current Allergies"
            add-label="Add New Allergy"
            empty-message="No allergies added yet."
            error-field="newAllergy"
            remove-method="removeAllergy"
            key-prefix="allergy"
            modal-title="Remove Allergy"
            context-label="allergies"
        />

        <x-settings.tag-list-section
            title="Food Preferences"
            :items="$userPreferences"
            color="emerald"
            wire-model="newPreference"
            add-action="addPreference"
            placeholder="Enter food preference"
            current-label="Current Preferences"
            add-label="Add New Preference"
            empty-message="No food preferences added yet."
            error-field="newPreference"
            remove-method="removePreference"
            key-prefix="preference"
            modal-title="Remove Food Preference"
            context-label="food preferences"
        />
    </div>
</div>
