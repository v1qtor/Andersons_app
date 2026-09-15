<div class="max-w-5xl mx-auto p-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 mb-8 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Staff Availability</h1>
            <p class="text-sm text-gray-500 mt-1">View and manage unavailability periods for all staff.</p>
        </div>
        <flux:button wire:click="openCreate()" variant="primary" icon="plus" class="sm:self-start">
            Add Period
        </flux:button>
    </div>

    {{-- Filters --}}
    <div class="bg-white border border-gray-200 rounded-xl p-4 mb-6 flex flex-wrap gap-4">
        <flux:field class="flex-1 min-w-[180px]">
            <flux:label>Filter by name</flux:label>
            <flux:input wire:model.live="filterName" type="text" placeholder="Search name..." icon="magnifying-glass" />
        </flux:field>
        <flux:field class="flex-1 min-w-[180px]">
            <flux:label>Filter by date</flux:label>
            <flux:input wire:model.live="filterDate" type="date" />
        </flux:field>
        @if($filterName || $filterDate)
            <div class="flex items-end">
                <flux:button wire:click="clearFilters" variant="ghost" size="sm" icon="x-mark">
                    Clear
                </flux:button>
            </div>
        @endif
    </div>

    {{-- Staff Cards --}}
    <div class="space-y-4">
        @forelse($this->filteredUsers as $user)
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                {{-- Card Header --}}
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 bg-gray-50">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-indigo-100 flex items-center justify-center text-sm font-bold text-indigo-700">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900 text-sm">{{ $user->name }}</p>
                            <p class="text-xs text-gray-500">{{ $user->role?->name }}</p>
                        </div>
                    </div>
                    <span class="text-xs bg-red-100 text-red-700 font-semibold px-2 py-0.5 rounded-full">
                        {{ $user->unavailabilityPeriods->count() }} period{{ $user->unavailabilityPeriods->count() !== 1 ? 's' : '' }}
                    </span>
                </div>

                {{-- Periods --}}
                @php
                    $periods = $user->unavailabilityPeriods->sortBy('start_date');
                    $upcoming = $periods->filter(fn($p) => $p->end_date->isFuture() || $p->start_date->isFuture());
                    $past = $periods->filter(fn($p) => $p->end_date->isPast());
                @endphp

                <div class="divide-y divide-gray-100">
                    @foreach($upcoming as $period)
                        <div class="flex items-center justify-between px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-2 h-2 rounded-full bg-red-400 flex-shrink-0"></div>
                                <div>
                                    <span class="text-sm font-medium text-gray-900">
                                        @if($period->start_date->format('Y-m-d') === $period->end_date->format('Y-m-d'))
                                            {{ $period->start_date->format('d M Y') }}
                                        @else
                                            {{ $period->start_date->format('d M Y') }} → {{ $period->end_date->format('d M Y') }}
                                        @endif
                                    </span>
                                    <span class="text-sm text-gray-400 ml-2">
                                        {{ $period->start_date->format('H:i') }} – {{ $period->end_date->format('H:i') }}
                                    </span>
                                    @if($period->description)
                                        <span class="text-xs text-gray-400 italic ml-2">— {{ $period->description }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex gap-1">
                                <flux:button wire:click="openEdit({{ $period->id }})" size="sm" variant="ghost" icon="pencil" />
                                <flux:button wire:click="confirmDelete({{ $period->id }})" size="sm" variant="ghost" icon="trash" />
                            </div>
                        </div>
                    @endforeach

                    @if($past->isNotEmpty())
                        <div class="px-5 pt-4 text-xs text-gray-500">Past</div>
                        @foreach($past as $period)
                            <div class="flex items-center justify-between px-5 py-3 bg-gray-50 opacity-75">
                                <div class="flex items-center gap-3">
                                    <div class="w-2 h-2 rounded-full bg-gray-400 flex-shrink-0"></div>
                                    <div>
                                        <span class="text-sm font-medium text-gray-600">
                                            @if($period->start_date->format('Y-m-d') === $period->end_date->format('Y-m-d'))
                                                {{ $period->start_date->format('d M Y') }}
                                            @else
                                                {{ $period->start_date->format('d M Y') }} → {{ $period->end_date->format('d M Y') }}
                                            @endif
                                        </span>
                                        <span class="text-sm text-gray-400 ml-2">
                                            {{ $period->start_date->format('H:i') }} – {{ $period->end_date->format('H:i') }}
                                        </span>
                                        @if($period->description)
                                            <span class="text-xs text-gray-400 italic ml-2">— {{ $period->description }}</span>
                                        @endif
                                    </div>
                                </div>
                                <span class="text-xs text-gray-400 italic">Read-only</span>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-16 bg-white border border-dashed border-gray-300 rounded-xl">
                <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <p class="text-gray-400 text-sm">No unavailability periods found.</p>
                @if($filterName || $filterDate)
                    <p class="text-gray-300 text-xs mt-1">Try clearing the filters.</p>
                @endif
            </div>
        @endforelse
    </div>

    {{-- Modal --}}
    <flux:modal wire:model="showModal" class="max-w-md w-full">
        <div class="p-6 space-y-5">
            <div>
                <flux:heading size="lg">
                    {{ $editingId ? 'Edit Unavailability Period' : 'Add Unavailability Period' }}
                </flux:heading>
                <p class="text-sm text-gray-500 mt-1">Set unavailability for a staff member.</p>
                @error('duplicate')
                    <div class="mt-3 text-sm text-red-600 font-medium">{{ $message }}</div>
                @enderror
            </div>

            {{-- Person --}}
            <flux:field>
                <flux:label>Person</flux:label>
                <flux:select wire:model="selectedUserId">
                    <option value="">Select a person</option>
                    @foreach($this->allUsers as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->role?->name }})</option>
                    @endforeach
                </flux:select>
                @error('selectedUserId') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            {{-- From --}}
            <div>
                <p class="text-sm font-semibold text-gray-700 mb-2">Unavailable from</p>
                <div class="grid grid-cols-2 gap-3">
                    <flux:field>
                        <flux:label>Date</flux:label>
                        <flux:input type="date" wire:model="startDate" min="{{ now()->format('Y-m-d') }}" />
                        @error('startDate') <flux:error>{{ $message }}</flux:error> @enderror
                    </flux:field>
                    <flux:field>
                        <flux:label>Time</flux:label>
                        <flux:input type="time" wire:model="startTime" />
                        @error('startTime') <flux:error>{{ $message }}</flux:error> @enderror
                    </flux:field>
                </div>
            </div>

            {{-- To --}}
            <div>
                <p class="text-sm font-semibold text-gray-700 mb-2">Unavailable until</p>
                <div class="grid grid-cols-2 gap-3">
                    <flux:field>
                        <flux:label>Date</flux:label>
                        <flux:input type="date" wire:model="endDate" min="{{ now()->format('Y-m-d') }}" />
                        @error('endDate') <flux:error>{{ $message }}</flux:error> @enderror
                    </flux:field>
                    <flux:field>
                        <flux:label>Time</flux:label>
                        <flux:input type="time" wire:model="endTime" />
                        @error('endTime') <flux:error>{{ $message }}</flux:error> @enderror
                    </flux:field>
                </div>
            </div>

            {{-- Description --}}
            <flux:field>
                <flux:label>Reason <span class="text-gray-400 font-normal">(optional)</span></flux:label>
                <flux:input type="text" wire:model="description" placeholder="e.g. Sick leave, holiday..." />
                @error('description') <flux:error>{{ $message }}</flux:error> @enderror
            </flux:field>

            <div class="flex gap-3 pt-1">
                <flux:button wire:click="save()" variant="primary" class="flex-1">
                    {{ $editingId ? 'Update Period' : 'Save Period' }}
                </flux:button>
                <flux:button wire:click="$set('showModal', false)" variant="ghost">
                    Cancel
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Delete Confirmation --}}
    <flux:modal name="delete-period-modal" :show="$showDeleteConfirm" wire:model="showDeleteConfirm">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Remove Period</flux:heading>
                <flux:subheading>Remove this unavailability period? This cannot be undone.</flux:subheading>
            </div>

            <div class="flex gap-2 justify-end">
                <flux:button variant="ghost" wire:click="closeDeleteConfirm">Cancel</flux:button>
                <flux:button variant="danger" wire:click="delete">Remove</flux:button>
            </div>
        </div>
    </flux:modal>
</div>