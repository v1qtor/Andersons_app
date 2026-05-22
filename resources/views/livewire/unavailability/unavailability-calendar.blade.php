<div class="max-w-2xl mx-auto p-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 mb-8 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">My Unavailability</h1>
            <p class="text-sm text-gray-500 mt-1">Mark the periods when you are not available to work.</p>
        </div>
        <flux:button wire:click="openCreate()" variant="primary" icon="plus" class="sm:self-start">
            Add Period
        </flux:button>
    </div>

    {{-- Periods List --}}
    <div class="space-y-3">
        @forelse($this->periods as $period)
            @php
                $isPast = $period->end_date->isPast();
            @endphp
            <div class="border rounded-xl p-4 flex items-start justify-between gap-4 shadow-sm {{ $isPast ? 'bg-gray-50 border-gray-200 opacity-75' : 'bg-white border-gray-200' }}">
                <div class="flex gap-4 items-start">
                    {{-- Date Badge --}}
                    <div class="flex-shrink-0 rounded-lg px-3 py-2 text-center min-w-[64px] {{ $isPast ? 'bg-gray-100 border border-gray-200' : 'bg-red-50 border border-red-200' }}">
                        <div class="text-xs font-semibold uppercase {{ $isPast ? 'text-gray-500' : 'text-red-500' }}">
                            {{ $period->start_date->format('M') }}
                        </div>
                        <div class="text-2xl font-bold leading-none {{ $isPast ? 'text-gray-600' : 'text-red-700' }}">
                            {{ $period->start_date->format('d') }}
                        </div>
                    </div>

                    {{-- Info --}}
                    <div>
                        <div class="font-semibold text-sm {{ $isPast ? 'text-gray-600' : 'text-gray-900' }}">
                            @if($period->start_date->format('Y-m-d') === $period->end_date->format('Y-m-d'))
                                {{ $period->start_date->format('l, d F Y') }}
                            @else
                                {{ $period->start_date->format('d M Y') }} → {{ $period->end_date->format('d M Y') }}
                            @endif
                        </div>
                        <div class="text-sm mt-0.5 flex items-center gap-1 {{ $isPast ? 'text-gray-400' : 'text-gray-500' }}">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ $period->start_date->format('H:i') }} – {{ $period->end_date->format('H:i') }}
                        </div>
                        @if($period->description)
                            <div class="text-xs mt-1 italic {{ $isPast ? 'text-gray-400' : 'text-gray-500' }}">{{ $period->description }}</div>
                        @endif
                    </div>
                </div>

                {{-- Actions --}}
                @if(! $isPast)
                    <div class="flex gap-1 flex-shrink-0">
                        <flux:button wire:click="openEdit({{ $period->id }})" size="sm" variant="ghost" icon="pencil" />
                        <flux:button wire:click="delete({{ $period->id }})" wire:confirm="Remove this period?" size="sm" variant="ghost" icon="trash" />
                    </div>
                @else
                    <span class="text-xs text-gray-400 italic">Read-only</span>
                @endif
            </div>
        @empty
            <div class="text-center py-16 bg-white border border-dashed border-gray-300 rounded-xl">
                <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p class="text-gray-400 text-sm">No unavailability periods added yet.</p>
                <p class="text-gray-300 text-xs mt-1">Click "Add Period" to mark when you're unavailable.</p>
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
                <p class="text-sm text-gray-500 mt-1">Set the dates and times when you will not be available.</p>
            </div>

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
                <flux:input type="text" wire:model="description" placeholder="e.g. Doctor appointment, holiday..." />
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
</div>