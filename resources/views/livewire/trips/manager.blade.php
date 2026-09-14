<div class="w-full max-w-5xl mx-auto space-y-6">
    <x-ui.flash-alert fixed="true" />

    <livewire:trips.trip-form-modal />

    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-2">Trips</h1>
            <p class="text-gray-600 dark:text-gray-400">Plan trips, track checkpoints, and keep an eye on who's away.</p>
        </div>
        @if ($canManageTrips)
            <x-flux.button variant="primary" wire:click="$dispatch('open-trip-form')" class="sm:self-start">
                + Plan Trip
            </x-flux.button>
        @endif
    </div>

    <x-ui.section-card>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search trips by name or description..." class="w-full px-4 py-2 border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                <select wire:model.live="statusFilter" class="w-full px-4 py-2 border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="all">All</option>
                    @foreach ($statusOptions as $status)
                        <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        @if ($search !== '' || $statusFilter !== 'all')
            <div class="mt-3">
                <button wire:click="clearFilters" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white underline">Clear filters</button>
            </div>
        @endif
    </x-ui.section-card>

    @if ($trips->isEmpty())
        <x-ui.section-card padding="p-12" class="text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
            </svg>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No trips found</h3>
            <p class="text-gray-600 dark:text-gray-400">{{ $canManageTrips ? 'Plan your first trip to get started.' : 'Check back once a trip has been planned.' }}</p>
        </x-ui.section-card>
    @else
        <div class="space-y-6">
            @foreach ($trips as $trip)
                <livewire:trips.trip-card :trip-id="$trip->id" :key="'trip-'.$trip->id" />
            @endforeach
        </div>

        <div class="mt-6">
            {{ $trips->links() }}
        </div>
    @endif
</div>
