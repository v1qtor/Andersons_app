<div class="w-full max-w-6xl mx-auto"
    x-data="{
        showRemoveModal: false,
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
    x-effect="document.body.style.overflow = showRemoveModal ? 'hidden' : ''"
>

    <x-settings.confirm-remove-modal />

    <!-- Toast Notifications -->
    <div
        x-data="{
            toasts: [],
            add(message, type) {
                const id = Date.now();
                this.toasts.push({ id, message, type, show: true });
                setTimeout(() => {
                    const t = this.toasts.find(t => t.id === id);
                    if (t) t.show = false;
                    setTimeout(() => { this.toasts = this.toasts.filter(t => t.id !== id); }, 600);
                }, 5000);
            }
        }"
        @toast.window="add($event.detail.message, $event.detail.type)"
        class="fixed top-4 right-4 z-50 flex flex-col gap-2 pointer-events-none"
    >
        <template x-for="toast in toasts" :key="toast.id">
            <div
                x-show="toast.show"
                x-transition:leave="transition ease-in duration-500"
                x-transition:leave-start="opacity-100 translate-x-0"
                x-transition:leave-end="opacity-0 translate-x-24"
                :class="toast.type === 'error'
                    ? 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800'
                    : 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800'"
                class="rounded-lg shadow-lg p-4 max-w-md"
            >
                <div class="flex items-center gap-3">
                    <template x-if="toast.type !== 'error'">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </template>
                    <template x-if="toast.type === 'error'">
                        <svg class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-9a1 1 0 012 0v4a1 1 0 01-2 0V9zm1-4a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd" />
                        </svg>
                    </template>
                    <p
                        :class="toast.type === 'error' ? 'text-red-800 dark:text-red-300' : 'text-green-800 dark:text-green-300'"
                        class="font-medium"
                        x-text="toast.message"
                    ></p>
                </div>
            </div>
        </template>
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
                        <!-- Email Toggle -->
                        <div class="flex justify-center w-20">
                            <button 
                                wire:click="toggleNotification('tripDelayAlerts', 'email')"
                                class="relative inline-flex h-5 w-9 items-center rounded-full border transition-all duration-200 outline-none focus-visible:ring-[3px] @if($notifications['tripDelayAlerts']['email']) bg-indigo-600 border-transparent focus-visible:ring-indigo-500/50 @else bg-gray-200 border-gray-300 dark:bg-neutral-700 dark:border-neutral-600 focus-visible:ring-gray-400/50 @endif disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <span class="inline-block h-4 w-4 transform rounded-full transition-transform duration-200 pointer-events-none block shrink-0 @if($notifications['tripDelayAlerts']['email']) translate-x-4 bg-white @else translate-x-0.5 bg-gray-50 dark:bg-gray-300 @endif" />
                            </button>
                        </div>
                        <!-- Popup Toggle -->
                        <div class="flex justify-center w-20">
                            <button 
                                wire:click="toggleNotification('tripDelayAlerts', 'popup')"
                                class="relative inline-flex h-5 w-9 items-center rounded-full border transition-all duration-200 outline-none focus-visible:ring-[3px] @if($notifications['tripDelayAlerts']['popup']) bg-indigo-600 border-transparent focus-visible:ring-indigo-500/50 @else bg-gray-200 border-gray-300 dark:bg-neutral-700 dark:border-neutral-600 focus-visible:ring-gray-400/50 @endif disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <span class="inline-block h-4 w-4 transform rounded-full transition-transform duration-200 pointer-events-none block shrink-0 @if($notifications['tripDelayAlerts']['popup']) translate-x-4 bg-white @else translate-x-0.5 bg-gray-50 dark:bg-gray-300 @endif" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Task Reminders -->
                <div class="flex items-center justify-between py-4 border-t border-gray-100 dark:border-neutral-700">
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Task Reminders</p>
                    </div>
                    <div class="flex gap-16">
                        <!-- Email Toggle -->
                        <div class="flex justify-center w-20">
                            <button 
                                wire:click="toggleNotification('taskReminders', 'email')"
                                class="relative inline-flex h-5 w-9 items-center rounded-full border transition-all duration-200 outline-none focus-visible:ring-[3px] @if($notifications['taskReminders']['email']) bg-indigo-600 border-transparent focus-visible:ring-indigo-500/50 @else bg-gray-200 border-gray-300 dark:bg-neutral-700 dark:border-neutral-600 focus-visible:ring-gray-400/50 @endif disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <span class="inline-block h-4 w-4 transform rounded-full transition-transform duration-200 pointer-events-none block shrink-0 @if($notifications['taskReminders']['email']) translate-x-4 bg-white @else translate-x-0.5 bg-gray-50 dark:bg-gray-300 @endif" />
                            </button>
                        </div>
                        <!-- Popup Toggle -->
                        <div class="flex justify-center w-20">
                            <button 
                                wire:click="toggleNotification('taskReminders', 'popup')"
                                class="relative inline-flex h-5 w-9 items-center rounded-full border transition-all duration-200 outline-none focus-visible:ring-[3px] @if($notifications['taskReminders']['popup']) bg-indigo-600 border-transparent focus-visible:ring-indigo-500/50 @else bg-gray-200 border-gray-300 dark:bg-neutral-700 dark:border-neutral-600 focus-visible:ring-gray-400/50 @endif disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <span class="inline-block h-4 w-4 transform rounded-full transition-transform duration-200 pointer-events-none block shrink-0 @if($notifications['taskReminders']['popup']) translate-x-4 bg-white @else translate-x-0.5 bg-gray-50 dark:bg-gray-300 @endif" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Receipt Approvals -->
                <div class="flex items-center justify-between py-4 border-t border-gray-100 dark:border-neutral-700">
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Receipt Approvals</p>
                    </div>
                    <div class="flex gap-16">
                        <!-- Email Toggle -->
                        <div class="flex justify-center w-20">
                            <button 
                                wire:click="toggleNotification('receiptApprovals', 'email')"
                                class="relative inline-flex h-5 w-9 items-center rounded-full border transition-all duration-200 outline-none focus-visible:ring-[3px] @if($notifications['receiptApprovals']['email']) bg-indigo-600 border-transparent focus-visible:ring-indigo-500/50 @else bg-gray-200 border-gray-300 dark:bg-neutral-700 dark:border-neutral-600 focus-visible:ring-gray-400/50 @endif disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <span class="inline-block h-4 w-4 transform rounded-full transition-transform duration-200 pointer-events-none block shrink-0 @if($notifications['receiptApprovals']['email']) translate-x-4 bg-white @else translate-x-0.5 bg-gray-50 dark:bg-gray-300 @endif" />
                            </button>
                        </div>
                        <!-- Popup Toggle -->
                        <div class="flex justify-center w-20">
                            <button 
                                wire:click="toggleNotification('receiptApprovals', 'popup')"
                                class="relative inline-flex h-5 w-9 items-center rounded-full border transition-all duration-200 outline-none focus-visible:ring-[3px] @if($notifications['receiptApprovals']['popup']) bg-indigo-600 border-transparent focus-visible:ring-indigo-500/50 @else bg-gray-200 border-gray-300 dark:bg-neutral-700 dark:border-neutral-600 focus-visible:ring-gray-400/50 @endif disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <span class="inline-block h-4 w-4 transform rounded-full transition-transform duration-200 pointer-events-none block shrink-0 @if($notifications['receiptApprovals']['popup']) translate-x-4 bg-white @else translate-x-0.5 bg-gray-50 dark:bg-gray-300 @endif" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Dinner Signup Confirmations -->
                <div class="flex items-center justify-between py-4 border-t border-gray-100 dark:border-neutral-700">
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Dinner Signup Confirmations</p>
                    </div>
                    <div class="flex gap-16">
                        <!-- Email Toggle -->
                        <div class="flex justify-center w-20">
                            <button 
                                wire:click="toggleNotification('dinnerSignups', 'email')"
                                class="relative inline-flex h-5 w-9 items-center rounded-full border transition-all duration-200 outline-none focus-visible:ring-[3px] @if($notifications['dinnerSignups']['email']) bg-indigo-600 border-transparent focus-visible:ring-indigo-500/50 @else bg-gray-200 border-gray-300 dark:bg-neutral-700 dark:border-neutral-600 focus-visible:ring-gray-400/50 @endif disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <span class="inline-block h-4 w-4 transform rounded-full transition-transform duration-200 pointer-events-none block shrink-0 @if($notifications['dinnerSignups']['email']) translate-x-4 bg-white @else translate-x-0.5 bg-gray-50 dark:bg-gray-300 @endif" />
                            </button>
                        </div>
                        <!-- Popup Toggle -->
                        <div class="flex justify-center w-20">
                            <button 
                                wire:click="toggleNotification('dinnerSignups', 'popup')"
                                class="relative inline-flex h-5 w-9 items-center rounded-full border transition-all duration-200 outline-none focus-visible:ring-[3px] @if($notifications['dinnerSignups']['popup']) bg-indigo-600 border-transparent focus-visible:ring-indigo-500/50 @else bg-gray-200 border-gray-300 dark:bg-neutral-700 dark:border-neutral-600 focus-visible:ring-gray-400/50 @endif disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <span class="inline-block h-4 w-4 transform rounded-full transition-transform duration-200 pointer-events-none block shrink-0 @if($notifications['dinnerSignups']['popup']) translate-x-4 bg-white @else translate-x-0.5 bg-gray-50 dark:bg-gray-300 @endif" />
                            </button>
                        </div>
                    </div>
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
