<div class="w-full max-w-6xl mx-auto" x-data="{ showAllergyModal: false, allergyId: null, allergyName: '' }">

    <!-- Allergy Remove Confirmation Modal -->
    <div
        x-show="showAllergyModal"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center"
        style="display: none;"
    >
        <!-- Backdrop -->
        <div
            class="absolute inset-0 bg-black/50"
            @click="showAllergyModal = false"
        ></div>

        <!-- Modal Box -->
        <div
            x-show="showAllergyModal"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative bg-white dark:bg-neutral-800 rounded-xl shadow-xl border border-gray-200 dark:border-neutral-700 p-6 w-full max-w-sm mx-4"
        >
            <div class="flex items-start gap-4 mb-5">
                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Remove Allergy</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Are you sure you want to remove <span class="font-semibold text-gray-700 dark:text-gray-200" x-text="'\'' + allergyName + '\''" ></span> from your allergies?
                    </p>
                </div>
            </div>
            <div class="flex justify-end gap-3">
                <button
                    type="button"
                    @click="showAllergyModal = false"
                    class="px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 bg-white dark:bg-neutral-700 border border-gray-300 dark:border-neutral-600 rounded-lg hover:bg-gray-50 dark:hover:bg-neutral-600 transition-colors"
                >Cancel</button>
                <button
                    type="button"
                    @click="$wire.removeAllergy(allergyId); showAllergyModal = false"
                    class="px-4 py-2 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors"
                >Remove</button>
            </div>
        </div>
    </div>
    <!-- Success Message Toast -->
    @if (session('status') || session('message'))
        <div
            x-data="{ show: true }"
            x-init="setTimeout(() => show = false, 5000)"
            x-show="show"
            x-transition:leave="transition ease-in duration-500"
            x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 translate-x-24"
            class="fixed top-4 right-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg shadow-lg p-4 max-w-md z-50"
        >
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <p class="text-green-800 dark:text-green-300 font-medium">{{ session('status') ?? session('message') }}</p>
            </div>
        </div>
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

        <!-- Allergies Section -->
        <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-gray-200 dark:border-neutral-700 p-8">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Allergies</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Current Allergies -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Current Allergies</h3>
                    @if ($userAllergies->isEmpty())
                        <p class="text-sm text-gray-400 dark:text-gray-500">No allergies added yet.</p>
                    @else
                        <div class="flex flex-wrap gap-2">
                            @foreach ($userAllergies as $allergy)
                                <span class="inline-flex items-center gap-1.5 rounded-full border border-indigo-200 bg-indigo-50 px-3 py-1 text-sm font-medium text-indigo-700 dark:border-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">
                                    {{ $allergy->name }}
                                    <button
                                        type="button"
                                        @click="allergyId = {{ $allergy->id }}; allergyName = '{{ addslashes($allergy->name) }}'; showAllergyModal = true"
                                        class="flex items-center justify-center w-6 h-6 rounded-full hover:bg-indigo-200 dark:hover:bg-indigo-700 transition-colors"
                                        aria-label="Remove {{ $allergy->name }}"
                                    >&times;</button>
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Add New Allergy -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Add New Allergy</h3>
                    <div class="flex gap-2">
                        <input
                            wire:model="newAllergy"
                            wire:keydown.enter.prevent="addAllergy"
                            type="text"
                            placeholder="Enter allergy name"
                            class="flex-1 px-4 py-2.5 border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors text-sm"
                        >
                        <button
                            wire:click="addAllergy"
                            type="button"
                            class="px-4 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-semibold text-sm whitespace-nowrap"
                        >+ Add</button>
                    </div>
                    @error('newAllergy')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
    </div>
</div>
