<div>
    <x-ui.detail-modal :show="$showModal" :title="$tripId ? 'Edit Trip' : 'Plan Trip'" closeAction="close" maxWidth="max-w-2xl" :scrollableBody="false" panelClass="max-h-[90vh] overflow-y-auto">
        <form wire:submit="save" class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Location / Name *</label>
                <input type="text" wire:model="name" placeholder="e.g., Yorkshire Moors National Park" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
                @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                <textarea wire:model="description" rows="2" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start *</label>
                    <input type="datetime-local" wire:model="start_date" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white" />
                    @error('start_date') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">End *</label>
                    <input type="datetime-local" wire:model="end_date" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white" />
                    @error('end_date') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category *</label>
                <select wire:model="trip_category_id" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white">
                    <option value="">Select…</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
                @error('trip_category_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Expected Return / Buffer Alert</label>
                <input type="datetime-local" wire:model="buffer_alert" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white" />
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">The household is alerted if the trip isn't marked returned by this time.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                <textarea wire:model="notes" rows="2" placeholder="Anything else worth noting for this trip" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Participants *</label>
                <div class="grid grid-cols-2 gap-2 max-h-40 overflow-y-auto">
                    @foreach ($users as $user)
                        <label class="flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-200 dark:border-neutral-600 cursor-pointer text-sm text-gray-900 dark:text-gray-100">
                            <input type="checkbox" wire:model="userIds" value="{{ $user->id }}" class="w-4 h-4 rounded">
                            {{ $user->name }}
                        </label>
                    @endforeach
                </div>
                @error('userIds') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            @if ($permanentCheckpoints->isNotEmpty())
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Permanent Checkpoints</label>
                    <div class="space-y-1 max-h-32 overflow-y-auto">
                        @foreach ($permanentCheckpoints as $cp)
                            <label class="flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-200 dark:border-neutral-600 cursor-pointer text-sm text-gray-900 dark:text-gray-100">
                                <input type="checkbox" wire:model="checkpointIds" value="{{ $cp->id }}" class="w-4 h-4 rounded">
                                <span class="font-medium">{{ $cp->location }}</span>
                                @if ($cp->address)<span class="text-gray-500 dark:text-gray-400">— {{ $cp->address }}</span>@endif
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Temporary Checkpoints <span class="font-normal text-gray-500">(only this trip)</span></label>
                    <button type="button" wire:click="addTempCheckpoint" class="text-sm font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700">+ Add</button>
                </div>
                <div class="space-y-3">
                    @foreach ($tempCheckpoints as $index => $temp)
                        <div wire:key="temp-cp-{{ $index }}" class="p-3 rounded-lg border border-dashed border-gray-300 dark:border-neutral-600 space-y-2">
                            <div class="flex gap-2">
                                <input type="text" wire:model="tempCheckpoints.{{ $index }}.name" placeholder="Checkpoint name" class="flex-1 px-3 py-2 rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white text-sm" />
                                <button type="button" wire:click="removeTempCheckpoint({{ $index }})" class="px-3 py-2 text-sm font-semibold text-red-600 hover:text-red-700">Remove</button>
                            </div>
                            <input type="text" wire:model.live.debounce.600ms="tempCheckpoints.{{ $index }}.address" placeholder="Address" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white text-sm" />
                            <x-trips.address-map :address="$temp['address'] ?? ''" height="160px" />
                        </div>
                    @endforeach
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Plus-Ones <span class="font-normal text-gray-500">(optional guests)</span></label>
                    <button type="button" wire:click="addPlusOne" class="text-sm font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700">+ Add Guest</button>
                </div>
                <div class="space-y-2">
                    @foreach ($plusOnes as $index => $plusOne)
                        <div wire:key="plus-one-{{ $index }}" class="p-3 rounded-lg border border-gray-200 dark:border-neutral-600 space-y-2">
                            <div class="flex gap-2">
                                <input type="text" wire:model="plusOnes.{{ $index }}.name" placeholder="Guest name" class="flex-1 px-3 py-2 rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white text-sm" />
                                <button type="button" wire:click="removePlusOne({{ $index }})" class="px-3 py-2 text-sm font-semibold text-red-600 hover:text-red-700">Remove</button>
                            </div>
                            <div class="flex gap-2">
                                <input type="email" wire:model="plusOnes.{{ $index }}.email" placeholder="Email (optional)" class="flex-1 px-3 py-2 rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white text-sm" />
                                <input type="tel" wire:model="plusOnes.{{ $index }}.phone" placeholder="Phone (optional)" class="flex-1 px-3 py-2 rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white text-sm" />
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" wire:click="close" class="px-5 py-2.5 rounded-lg border border-gray-300 dark:border-neutral-600 text-gray-700 dark:text-gray-300 font-semibold hover:bg-gray-50 dark:hover:bg-neutral-700">Cancel</button>
                <x-flux.button type="submit" variant="primary">{{ $tripId ? 'Save Changes' : 'Plan Trip' }}</x-flux.button>
            </div>
        </form>
    </x-ui.detail-modal>
</div>
