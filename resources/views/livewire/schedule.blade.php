<section class="w-full">
    <div class="max-w-7xl mx-auto">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
            <div>
                <flux:heading size="xl">{{ __('Schedule') }}</flux:heading>
                <flux:subheading>{{ __('Household calendar with tasks, meals and trips') }}</flux:subheading>
            </div>

            {{-- View Toggle --}}
            <div class="flex items-center gap-2">
                <flux:button size="sm" :variant="$view === 'day' ? 'primary' : 'ghost'" wire:click="setView('day')">
                    {{ __('Day') }}
                </flux:button>
                <flux:button size="sm" :variant="$view === 'week' ? 'primary' : 'ghost'" wire:click="setView('week')">
                    {{ __('Week') }}
                </flux:button>
                <flux:button size="sm" :variant="$view === 'month' ? 'primary' : 'ghost'" wire:click="setView('month')">
                    {{ __('Month') }}
                </flux:button>
            </div>
        </div>

        {{-- Navigation + Period Label --}}
        <div class="rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-zinc-800 p-6 mb-6">
            <div class="flex items-center justify-between mb-6">
                <flux:button variant="ghost" size="sm" wire:click="previousPeriod" icon="chevron-left" />

                <div class="flex items-center gap-3">
                    <h3 class="text-xl font-bold text-neutral-900 dark:text-neutral-100">
                        {{ $this->periodLabel }}
                    </h3>
                    <flux:button variant="ghost" size="sm" wire:click="goToToday">
                        {{ __('Today') }}
                    </flux:button>
                </div>

                <flux:button variant="ghost" size="sm" wire:click="nextPeriod" icon="chevron-right" />
            </div>

            {{-- People Filter --}}
            <div class="mb-6">
                <div class="flex items-center gap-2 mb-3">
                    <flux:icon name="funnel" class="size-5 text-neutral-500" />
                    <span class="font-semibold text-sm text-neutral-700 dark:text-neutral-300">{{ __('Filter by person:') }}</span>
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach ($users as $user)
                        @php
                            $isSelected = in_array($user->userId, $selectedPeople);
                            $roleColor = $user->role?->color ?? '#6366f1';
                        @endphp
                        <button
                            wire:click="togglePerson({{ $user->userId }})"
                            class="px-3 py-1.5 rounded-lg border-2 text-sm font-medium transition-all"
                            style="
                                border-color: {{ $roleColor }};
                                background-color: {{ $isSelected ? $roleColor : 'transparent' }};
                                color: {{ $isSelected ? '#fff' : $roleColor }};
                            "
                        >
                            {{ $user->name }}
                        </button>
                    @endforeach

                    @if (count($selectedPeople) > 0)
                        <flux:button variant="ghost" size="sm" wire:click="clearFilters">
                            {{ __('Clear Filters') }}
                        </flux:button>
                    @endif
                </div>
            </div>

            {{-- ========== MONTH VIEW ========== --}}
            @if ($view === 'month')
                <div class="grid grid-cols-7 gap-1.5">
                    {{-- Day headers --}}
                    @foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $dayName)
                        <div class="text-center text-xs font-semibold text-neutral-500 dark:text-neutral-400 py-2">
                            {{ __($dayName) }}
                        </div>
                    @endforeach

                    {{-- Calendar cells --}}
                    @foreach ($calendarDays as $dayNum)
                        @if ($dayNum === null)
                            <div class="aspect-square"></div>
                        @else
                            @php
                                $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $dayNum);
                                $dayEvents = $eventsByDate[$dateStr] ?? [];
                                $hasTasks = ! empty($dayEvents['tasks'] ?? []);
                                $hasMeals = ! empty($dayEvents['meals'] ?? []);
                                $hasTrips = ! empty($dayEvents['trips'] ?? []);
                                $hasAny = $hasTasks || $hasMeals || $hasTrips;
                                $isToday = $dateStr === $today;
                            @endphp
                            <button
                                wire:click="openDay('{{ $dateStr }}')"
                                class="aspect-square rounded-lg border p-1.5 text-left transition-all hover:shadow-md cursor-pointer
                                    {{ $isToday
                                        ? 'bg-indigo-50 border-indigo-500 border-2 dark:bg-indigo-950 dark:border-indigo-400'
                                        : ($hasAny
                                            ? 'bg-neutral-50 border-neutral-300 dark:bg-zinc-700/50 dark:border-neutral-600'
                                            : 'bg-white border-neutral-200 dark:bg-zinc-800 dark:border-neutral-700') }}"
                            >
                                <div class="text-xs font-semibold mb-0.5 {{ $isToday ? 'text-indigo-600 dark:text-indigo-400' : 'text-neutral-900 dark:text-neutral-100' }}">
                                    {{ $dayNum }}
                                </div>
                                <div class="space-y-0.5 overflow-hidden">
                                    @if ($hasTrips)
                                        @foreach (array_slice($dayEvents['trips'], 0, 1) as $trip)
                                            <div class="text-[10px] leading-tight px-1 py-0.5 rounded bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300 truncate">
                                                ⛺ {{ $trip->name }}
                                            </div>
                                        @endforeach
                                    @endif
                                    @if ($hasTasks)
                                        @foreach (array_slice($dayEvents['tasks'], 0, 2) as $task)
                                            <div class="text-[10px] leading-tight px-1 py-0.5 rounded truncate {{ $task->isComplete ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300' }}">
                                                {{ $task->title }}
                                            </div>
                                        @endforeach
                                        @if (count($dayEvents['tasks']) > 2)
                                            <div class="text-[10px] text-neutral-500 px-1">
                                                +{{ count($dayEvents['tasks']) - 2 }} {{ __('more') }}
                                            </div>
                                        @endif
                                    @endif
                                    @if ($hasMeals)
                                        @foreach (array_slice($dayEvents['meals'], 0, 1) as $meal)
                                            <div class="text-[10px] leading-tight px-1 py-0.5 rounded bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300 truncate">
                                                🍽️ {{ $meal->meal?->name }}
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </button>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>
