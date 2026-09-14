<div class="w-full max-w-5xl mx-auto space-y-6">
    <x-ui.flash-alert fixed="true" />

    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-2">Saved Checkpoints</h1>
            <p class="text-gray-600 dark:text-gray-400">Reusable locations, organized into folders, ready to add to any trip.</p>
        </div>
        <div class="flex gap-3 sm:self-start">
            <a href="{{ route('trips.index') }}" wire:navigate class="px-4 py-2.5 rounded-lg border border-gray-300 dark:border-neutral-600 text-gray-700 dark:text-gray-300 font-semibold hover:bg-gray-50 dark:hover:bg-neutral-700">
                Back to Trips
            </a>
            <x-flux.button variant="primary" wire:click="openCreate">+ Create Checkpoint</x-flux.button>
        </div>
    </div>

    <x-ui.section-card>
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search checkpoints and folders..." class="w-full px-4 py-2 border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
    </x-ui.section-card>

    @if ($folders->isEmpty() && $unassignedCheckpoints->isEmpty())
        <x-ui.section-card padding="p-12" class="text-center">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No checkpoints yet</h3>
            <p class="text-gray-600 dark:text-gray-400">Create checkpoints here, then add them to trips.</p>
        </x-ui.section-card>
    @else
        @foreach ($folders as $folder)
            @continue(($checkpointsByFolder[$folder->id] ?? collect())->isEmpty())
            <x-ui.section-card wire:key="folder-{{ $folder->id }}">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2 mb-4">
                    📁 {{ $folder->name }}
                    <span class="text-sm font-normal text-gray-500 dark:text-gray-400">({{ $checkpointsByFolder[$folder->id]->count() }})</span>
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ($checkpointsByFolder[$folder->id] as $checkpoint)
                        @include('livewire.checkpoints._card', ['checkpoint' => $checkpoint])
                    @endforeach
                </div>
            </x-ui.section-card>
        @endforeach

        @if ($unassignedCheckpoints->isNotEmpty())
            <x-ui.section-card>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Unassigned Checkpoints</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ($unassignedCheckpoints as $checkpoint)
                        @include('livewire.checkpoints._card', ['checkpoint' => $checkpoint])
                    @endforeach
                </div>
            </x-ui.section-card>
        @endif
    @endif

    <x-ui.detail-modal :show="$showModal" :title="$checkpointId ? 'Edit Checkpoint' : 'Create Checkpoint'" closeAction="close">
        <form wire:submit="save" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Checkpoint Name *</label>
                <input type="text" wire:model="location" placeholder="e.g., Malham Cove" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white" />
                @error('location') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                <input type="text" wire:model="description" placeholder="e.g., Natural limestone formation" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Address</label>
                <input type="text" wire:model.live.debounce.600ms="address" placeholder="e.g., Malham Cove, Settle BD24 9PT" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white mb-2" />
                <x-trips.address-map :address="$address" height="160px" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Folder</label>
                <select wire:model="folderId" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white">
                    <option value="">No folder</option>
                    @foreach ($folders as $folder)
                        <option value="{{ $folder->id }}">{{ $folder->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Or Create New Folder</label>
                <input type="text" wire:model="newFolderName" placeholder="e.g., Lake District Locations" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white" />
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" wire:click="close" class="px-5 py-2.5 rounded-lg border border-gray-300 dark:border-neutral-600 text-gray-700 dark:text-gray-300 font-semibold hover:bg-gray-50 dark:hover:bg-neutral-700">Cancel</button>
                <x-flux.button type="submit" variant="primary">{{ $checkpointId ? 'Save Changes' : 'Save Checkpoint' }}</x-flux.button>
            </div>
        </form>
    </x-ui.detail-modal>
</div>
