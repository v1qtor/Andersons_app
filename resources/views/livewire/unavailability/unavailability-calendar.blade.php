<div class="p-6">
        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900">My Unavailability</h1>
            <flux:button wire:click="openCreate()" variant="primary" icon="plus">
                Add Period
            </flux:button>
        </div>

        {{-- Week Navigation --}}
        <div class="flex items-center justify-between mb-4">
            <flux:button wire:click="previousWeek()" variant="ghost" icon="chevron-left">Previous</flux:button>
            <div class="flex items-center gap-3">
                <span class="text-lg font-semibold text-gray-900">
                    {{ \Carbon\Carbon::parse($currentWeekStart)->format('d M') }}
                    –
                    {{ \Carbon\Carbon::parse($currentWeekStart)->addDays(6)->format('d M Y') }}
                </span>
                <flux:button wire:click="goToCurrentWeek()" variant="ghost" size="sm">This Week</flux:button>
            </div>
            <flux:button wire:click="nextWeek()" variant="ghost" icon-trailing="chevron-right">Next</flux:button>
        </div>

        {{-- Calendar Table --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-x-auto mb-8">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 w-24">Week</th>
                        @foreach($this->weekDays as $day)
                            <th class="px-3 py-3 text-center text-sm font-semibold text-gray-700 min-w-[110px]">
                                <div>{{ \Carbon\Carbon::parse($day)->format('D') }}</div>
                                <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($day)->format('d M') }}</div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="px-4 py-3 text-sm font-medium text-gray-600">
                            {{ Auth::user()->name }}
                        </td>
                        @foreach($this->weekDays as $day)
                            @php $period = $this->getPeriodForDate($day); @endphp
                            <td class="px-2 py-2 text-center">
                                @if($period)
                                    <div class="relative group">
                                        <div class="bg-red-100 border border-red-400 rounded-lg p-2 text-xs text-red-800 cursor-pointer"
                                             wire:click="openEdit({{ $period->id }})">
                                            <div class="font-semibold">Unavailable</div>
                                            @if($period->description)
                                                <div class="truncate max-w-[90px]">{{ $period->description }}</div>
                                            @endif
                                            <div class="text-gray-500 mt-1">
                                                {{ $period->start_date->format('H:i') }} – {{ $period->end_date->format('H:i') }}
                                            </div>
                                        </div>
                                        <button wire:click="delete({{ $period->id }})"
                                                wire:confirm="Remove this unavailability period?"
                                                class="absolute -top-1 -right-1 hidden group-hover:flex w-5 h-5 bg-red-500 text-white rounded-full items-center justify-center text-xs hover:bg-red-700">
                                            ×
                                        </button>
                                    </div>
                                @else
                                    <button wire:click="openCreate('{{ $day }}')"
                                            class="w-full h-10 rounded-lg border-2 border-dashed border-gray-300 hover:border-red-400 hover:bg-red-50 transition-colors text-gray-400 hover:text-red-500 text-xs">
                                        + Add
                                    </button>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- All periods list --}}
        <h2 class="text-lg font-semibold text-gray-900 mb-3">All My Unavailability Periods</h2>
        @forelse($this->periods as $period)
            <div class="flex items-center justify-between p-4 bg-white border border-gray-200 rounded-lg mb-2">
                <div>
                    <span class="font-semibold text-gray-900">
                        {{ $period->start_date->format('d M Y H:i') }}
                        @if($period->start_date->format('Y-m-d') !== $period->end_date->format('Y-m-d'))
                            → {{ $period->end_date->format('d M Y H:i') }}
                        @else
                            – {{ $period->end_date->format('H:i') }}
                        @endif
                    </span>
                    @if($period->description)
                        <p class="text-sm text-gray-500">{{ $period->description }}</p>
                    @endif
                </div>
                <div class="flex gap-2">
                    <flux:button wire:click="openEdit({{ $period->id }})" size="sm" variant="ghost" icon="pencil" />
                    <flux:button wire:click="delete({{ $period->id }})" wire:confirm="Remove this period?" size="sm" variant="ghost" icon="trash" />
                </div>
            </div>
        @empty
            <p class="text-gray-500 text-sm">No unavailability periods added yet.</p>
        @endforelse

        {{-- Modal --}}
        <flux:modal wire:model="showModal" class="max-w-md w-full">
            <div class="p-6 space-y-4">
                <flux:heading size="lg">
                    {{ $editingId ? 'Edit Period' : 'Add Unavailability Period' }}
                </flux:heading>

                <div class="grid grid-cols-2 gap-3">
                    <flux:field>
                        <flux:label>Start Date</flux:label>
                        <flux:input type="date" wire:model="startDate" />
                        @error('startDate') <flux:error>{{ $message }}</flux:error> @enderror
                    </flux:field>
                    <flux:field>
                        <flux:label>Start Time</flux:label>
                        <flux:input type="time" wire:model="startTime" />
                        @error('startTime') <flux:error>{{ $message }}</flux:error> @enderror
                    </flux:field>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <flux:field>
                        <flux:label>End Date</flux:label>
                        <flux:input type="date" wire:model="endDate" />
                        @error('endDate') <flux:error>{{ $message }}</flux:error> @enderror
                    </flux:field>
                    <flux:field>
                        <flux:label>End Time</flux:label>
                        <flux:input type="time" wire:model="endTime" />
                        @error('endTime') <flux:error>{{ $message }}</flux:error> @enderror
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Description (optional)</flux:label>
                    <flux:input type="text" wire:model="description" placeholder="e.g. Doctor appointment" />
                    @error('description') <flux:error>{{ $message }}</flux:error> @enderror
                </flux:field>

                <div class="flex gap-3 pt-2">
                    <flux:button wire:click="save()" variant="primary" class="flex-1">
                        {{ $editingId ? 'Update' : 'Save' }}
                    </flux:button>
                    <flux:button wire:click="$set('showModal', false)" variant="ghost">
                        Cancel
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    </div>