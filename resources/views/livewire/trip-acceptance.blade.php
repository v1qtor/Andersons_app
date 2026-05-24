<div>
    @if (!$acceptTrip)
        <button wire:click="$toggle('showModal')" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
            Accept Trip
        </button>

        @if ($showModal)
            <dialog open class="rounded-2xl shadow-2xl w-full max-w-lg p-0 border-0">
                <form class="bg-white rounded-2xl p-8">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-3xl font-extrabold">Accept Trip</h2>
                        <button type="button" wire:click="$toggle('showModal')" class="text-gray-400 hover:text-gray-700 text-3xl">&times;</button>
                    </div>

                    <div class="mb-6">
                        <p class="text-gray-700 mb-4">Are you going to join <strong>{{ $trip->name }}</strong> ({{ $trip->start_date->format('M j, Y') }} - {{ $trip->end_date->format('M j, Y') }})?</p>
                    </div>

                    <!-- Plus-Ones Section -->
                    <div class="mb-6">
                        <label class="block text-lg font-semibold mb-3">Add Plus-Ones (optional)</label>
                        <div class="space-y-3">
                            @forelse ($plusOnes as $index => $plusOne)
                                <div class="border border-gray-300 rounded-lg p-4">
                                    <div class="flex justify-between items-center mb-3">
                                        <span class="font-semibold">Guest {{ $index + 1 }}</span>
                                        <button type="button" wire:click="removePlusOne({{ $index }})" class="text-red-600 hover:text-red-800">Remove</button>
                                    </div>
                                    <input type="text" wire:model="plusOnes.{{ $index }}.name" placeholder="Name *" class="w-full px-4 py-2 rounded-lg border border-gray-300 mb-2" />
                                    <input type="email" wire:model="plusOnes.{{ $index }}.email" placeholder="Email" class="w-full px-4 py-2 rounded-lg border border-gray-300 mb-2" />
                                    <input type="tel" wire:model="plusOnes.{{ $index }}.phone" placeholder="Phone" class="w-full px-4 py-2 rounded-lg border border-gray-300" />
                                </div>
                            @empty
                                <p class="text-gray-500 text-sm">No plus-ones added yet</p>
                            @endforelse
                        </div>

                        <button type="button" wire:click="addPlusOne" class="mt-3 w-full bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded">
                            + Add Another Guest
                        </button>
                    </div>

                    <div class="flex justify-end gap-4">
                        <button type="button" wire:click="acceptTripWithPlusOnes" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded">Accept & Continue</button>
                        <button type="button" wire:click="$toggle('showModal')" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 px-8 rounded">Cancel</button>
                    </div>
                </form>
            </dialog>
        @endif
    @else
        <div class="flex items-center gap-3">
            <span class="text-green-600 font-bold text-lg">✓ Accepted</span>
            <button wire:click="rejectTrip" onclick="return confirm('Are you sure you want to decline this trip?')" class="bg-red-100 hover:bg-red-200 text-red-700 font-bold py-2 px-4 rounded text-sm">
                Decline Trip
            </button>
        </div>
    @endif
</div>
