<div>
    @if (!$acceptTrip)
        <button wire:click="$toggle('showModal')" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm py-2 px-4 rounded-lg">
            Accept Trip
        </button>

        <x-ui.detail-modal :show="$showModal" title="Accept Trip" closeAction="$toggle('showModal')">
            <p class="text-gray-700 dark:text-gray-300 mb-6">
                Are you going to join <strong>{{ $trip->name }}</strong>
                ({{ $trip->start_date->format('M j, Y') }} - {{ $trip->end_date->format('M j, Y') }})?
            </p>

            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Add Plus-Ones (optional)</label>
                <div class="space-y-3">
                    @forelse ($plusOnes as $index => $plusOne)
                        <div class="border border-gray-200 dark:border-neutral-600 rounded-lg p-4">
                            <div class="flex justify-between items-center mb-3">
                                <span class="font-semibold text-gray-900 dark:text-white">Guest {{ $index + 1 }}</span>
                                <button type="button" wire:click="removePlusOne({{ $index }})" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 text-sm font-semibold">Remove</button>
                            </div>
                            <input type="text" wire:model="plusOnes.{{ $index }}.name" placeholder="Name *" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white mb-2" />
                            <input type="email" wire:model="plusOnes.{{ $index }}.email" placeholder="Email" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white mb-2" />
                            <input type="tel" wire:model="plusOnes.{{ $index }}.phone" placeholder="Phone" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white" />
                        </div>
                    @empty
                        <p class="text-gray-500 dark:text-gray-400 text-sm">No plus-ones added yet</p>
                    @endforelse
                </div>

                <button type="button" wire:click="addPlusOne" class="mt-3 w-full bg-gray-100 dark:bg-neutral-700 hover:bg-gray-200 dark:hover:bg-neutral-600 text-gray-800 dark:text-gray-200 font-semibold py-2 px-4 rounded-lg">
                    + Add Another Guest
                </button>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" wire:click="$toggle('showModal')" class="px-5 py-2.5 rounded-lg border border-gray-300 dark:border-neutral-600 text-gray-700 dark:text-gray-300 font-semibold hover:bg-gray-50 dark:hover:bg-neutral-700">Cancel</button>
                <x-flux.button wire:click="acceptTripWithPlusOnes" variant="primary">Accept & Continue</x-flux.button>
            </div>
        </x-ui.detail-modal>
    @else
        <div class="flex items-center gap-3">
            <span class="text-green-600 dark:text-green-400 font-semibold text-sm">✓ Accepted</span>
            <button wire:click="confirmDecline" class="bg-red-100 dark:bg-red-900/20 hover:bg-red-200 dark:hover:bg-red-900/40 text-red-700 dark:text-red-400 font-semibold py-1.5 px-3 rounded-lg text-sm">
                Decline Trip
            </button>
        </div>

        <x-confirm-action-modal
            :show="$confirmingDecline"
            title="Decline Trip"
            message="Are you sure you want to decline this trip? Any plus-ones you added will be removed."
            cancelAction="closeDeclineConfirm"
            confirmAction="rejectTrip"
            confirmLabel="Decline Trip"
            confirmTone="danger"
        />
    @endif
</div>
