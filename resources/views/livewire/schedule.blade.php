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
                            $isSelected = in_array($user->id, $selectedPeople);
                            $roleColor = $user->role?->color ?? '#6366f1';
                        @endphp
                        <button
                            wire:click="togglePerson({{ $user->id }})"
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
                                class="aspect-square rounded-lg border p-1.5 text-left transition-all hover:shadow-md cursor-pointer flex flex-col
                                    {{ $isToday
                                        ? 'bg-indigo-50 border-indigo-500 border-2 dark:bg-indigo-950 dark:border-indigo-400'
                                        : ($hasAny
                                            ? 'bg-neutral-50 border-neutral-300 dark:bg-zinc-700/50 dark:border-neutral-600'
                                            : 'bg-white border-neutral-200 dark:bg-zinc-800 dark:border-neutral-700') }}"
                            >
                                <div class="flex items-start justify-start w-full">
                                    <span class="inline-flex items-center justify-center text-xs font-bold w-6 h-6 rounded-full {{ $isToday ? 'bg-indigo-600 text-white dark:bg-indigo-500' : 'text-neutral-900 dark:text-neutral-100' }}">
                                        {{ $dayNum }}
                                    </span>
                                </div>
                                <div class="space-y-0.5 overflow-hidden flex-1 w-full">
                                    @if ($hasTrips)
                                        @foreach (array_slice($dayEvents['trips'], 0, 1) as $trip)
                                            <div class="text-[10px] leading-tight px-1 py-0.5 rounded bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300 truncate">
                                                ⛺ {{ $trip->name }}
                                            </div>
                                        @endforeach
                                    @endif
                                    @if ($hasTasks)
                                        @foreach (array_slice($dayEvents['tasks'], 0, 2) as $task)
                                            <div class="text-[10px] leading-tight px-1 py-0.5 rounded truncate {{ $task->is_complete ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300' }}">
                                                <span class="font-medium">{{ $task->date ? $task->date->format('H:i') : $task->start_date->format('H:i') }}</span> {{ $task->title }}
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
                                                🍽️ {{ $meal->date_time->format('H:i') }} {{ $meal->meal?->name }}
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </button>
                        @endif
                    @endforeach
                </div>

                {{-- ========== WEEK VIEW (Outlook-style) ========== --}}
            @elseif ($view === 'week')
                @php
                    $timeSlots = [];
                    for ($h = 0; $h < 24; $h++) {
                        $timeSlots[] = sprintf('%02d:00', $h);
                    }

                    // Group events by day and hour for positioning
                    $weekEventsByDayHour = [];
                    $allDayEventsByDay = [];
                    foreach ($weekDays as $wd) {
                        $ds = $wd->format('Y-m-d');
                        $de = $eventsByDate[$ds] ?? [];

                        // Trips are all-day events
                        $allDayEventsByDay[$ds] = $de['trips'] ?? [];

                        // Tasks by hour
                        foreach ($de['tasks'] ?? [] as $task) {
                            $taskTime = $task->date ?? $task->start_date;
                            $hour = (int) $taskTime->format('H');
                            $weekEventsByDayHour[$ds][$hour]['tasks'][] = $task;
                        }

                        // Meals by hour
                        foreach ($de['meals'] ?? [] as $meal) {
                            $hour = (int) $meal->date_time->format('H');
                            $weekEventsByDayHour[$ds][$hour]['meals'][] = $meal;
                        }
                    }
                    $hasAnyAllDay = collect($allDayEventsByDay)->filter(fn($v) => !empty($v))->isNotEmpty();
                @endphp
                <div class="overflow-x-auto">
                    <div class="min-w-[800px]">
                        {{-- Day headers --}}
                        <div class="grid grid-cols-[60px_repeat(7,1fr)] border-b border-neutral-200 dark:border-neutral-700">
                            <div class="py-2"></div>
                            @foreach ($weekDays as $weekDay)
                                @php
                                    $dateStr = $weekDay->format('Y-m-d');
                                    $isToday = $dateStr === $today;
                                @endphp
                                <button
                                    wire:click="openDay('{{ $dateStr }}')"
                                    class="py-2 text-center font-semibold text-sm cursor-pointer transition-colors border-l border-neutral-200 dark:border-neutral-700
                                        {{ $isToday
                                            ? 'bg-indigo-600 text-white dark:bg-indigo-500'
                                            : 'bg-neutral-50 text-neutral-700 hover:bg-neutral-100 dark:bg-zinc-700 dark:text-neutral-300 dark:hover:bg-zinc-600' }}"
                                >
                                    <div class="text-xs uppercase">{{ $weekDay->format('D') }}</div>
                                    <div class="text-lg {{ $isToday ? 'font-bold' : '' }}">{{ $weekDay->format('j') }}</div>
                                </button>
                            @endforeach
                        </div>

                        {{-- All-day events row --}}
                        @if ($hasAnyAllDay)
                            <div class="grid grid-cols-[60px_repeat(7,1fr)] border-b border-neutral-200 dark:border-neutral-700">
                                <div class="py-2 text-[10px] text-neutral-500 dark:text-neutral-400 text-right pr-2 flex items-center justify-end">
                                    {{ __('All day') }}
                                </div>
                                @foreach ($weekDays as $weekDay)
                                    @php $dateStr = $weekDay->format('Y-m-d'); @endphp
                                    <button
                                        wire:click="openDay('{{ $dateStr }}')"
                                        class="border-l border-neutral-200 dark:border-neutral-700 p-1 min-h-[40px] cursor-pointer hover:bg-neutral-50 dark:hover:bg-zinc-700/50 transition-colors"
                                    >
                                        @foreach ($allDayEventsByDay[$dateStr] ?? [] as $trip)
                                            <div class="text-[10px] px-1.5 py-0.5 rounded bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300 truncate mb-0.5">
                                                ⛺ {{ $trip->name }}
                                            </div>
                                        @endforeach
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        {{-- Time grid --}}
                        <div class="max-h-[600px] overflow-y-auto">
                            @foreach ($timeSlots as $slot)
                                @php $slotHour = (int) substr($slot, 0, 2); @endphp
                                <div class="grid grid-cols-[60px_repeat(7,1fr)] border-b border-neutral-100 dark:border-neutral-700/50 min-h-[52px]">
                                    <div class="text-[11px] text-neutral-400 dark:text-neutral-500 text-right pr-2 pt-1 select-none">
                                        {{ $slot }}
                                    </div>
                                    @foreach ($weekDays as $weekDay)
                                        @php
                                            $dateStr = $weekDay->format('Y-m-d');
                                            $hourEvents = $weekEventsByDayHour[$dateStr][$slotHour] ?? [];
                                            $isToday = $dateStr === $today;
                                        @endphp
                                        <button
                                            wire:click="openDay('{{ $dateStr }}')"
                                            class="border-l border-neutral-100 dark:border-neutral-700/50 p-0.5 cursor-pointer transition-colors
                                                {{ $isToday ? 'bg-indigo-50/50 dark:bg-indigo-950/20' : 'hover:bg-neutral-50 dark:hover:bg-zinc-700/30' }}"
                                        >
                                            @foreach ($hourEvents['tasks'] ?? [] as $task)
                                                <div class="text-[10px] leading-tight px-1.5 py-0.5 rounded mb-0.5 truncate {{ $task->is_complete ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300' }}">
                                                    <span class="font-semibold">{{ ($task->date ?? $task->start_date)->format('H:i') }}</span> {{ $task->title }}
                                                </div>
                                            @endforeach
                                            @foreach ($hourEvents['meals'] ?? [] as $meal)
                                                <div class="text-[10px] leading-tight px-1.5 py-0.5 rounded mb-0.5 bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300 truncate">
                                                    <span class="font-semibold">{{ $meal->date_time->format('H:i') }}</span> 🍽️ {{ $meal->meal?->name }}
                                                </div>
                                            @endforeach
                                        </button>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- ========== DAY VIEW ========== --}}
            @elseif ($view === 'day')
                @php
                    $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
                    $dayEvents = $eventsByDate[$dateStr] ?? [];
                @endphp
                <div class="space-y-4">
                    {{-- Trips --}}
                    @foreach ($dayEvents['trips'] ?? [] as $trip)
                        <div class="p-4 rounded-lg border-2 border-orange-300 bg-orange-50 dark:bg-orange-900/20 dark:border-orange-700">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-lg">⛺</span>
                                <span class="font-bold text-neutral-900 dark:text-neutral-100">{{ $trip->name }}</span>
                                @if ($trip->tripCategory)
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-orange-200 text-orange-800 dark:bg-orange-800 dark:text-orange-200">{{ $trip->tripCategory->name }}</span>
                                @endif
                            </div>
                            @if ($trip->description)
                                <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-2">{{ $trip->description }}</p>
                            @endif
                            <div class="text-sm text-neutral-500 dark:text-neutral-400 mb-2">
                                {{ $trip->start_date->format('j M') }} → {{ $trip->end_date->format('j M Y') }}
                            </div>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($trip->users as $u)
                                    <span class="px-2.5 py-1 rounded-full text-white text-xs font-medium" style="background-color: {{ $u->role?->color ?? '#6366f1' }}">
                                        {{ $u->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    {{-- Tasks --}}
                    @foreach ($dayEvents['tasks'] ?? [] as $task)
                        <div class="p-4 rounded-lg border-2 {{ $task->is_complete ? 'border-green-300 bg-green-50 dark:bg-green-900/20 dark:border-green-700' : 'border-blue-300 bg-blue-50 dark:bg-blue-900/20 dark:border-blue-700' }}">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-bold text-neutral-900 dark:text-neutral-100">{{ $task->is_complete ? '✓' : '○' }} {{ $task->title }}</span>
                                <span class="text-sm text-neutral-500 dark:text-neutral-400">{{ ($task->date ?? $task->start_date)->format('H:i') }}</span>
                                @if ($task->taskPriority)
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-neutral-200 text-neutral-700 dark:bg-neutral-700 dark:text-neutral-300">{{ $task->taskPriority->name }}</span>
                                @endif
                            </div>
                            @if ($task->description)
                                <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-2">{{ $task->description }}</p>
                            @endif
                            <div class="flex items-center gap-3 text-sm text-neutral-500 dark:text-neutral-400 mb-2">
                                @if ($task->taskCategory)
                                    <span>{{ $task->taskCategory->name }}</span>
                                @endif
                                @if ($task->locations->isNotEmpty())
                                    <span>📍 {{ $task->locations->pluck('name')->join(', ') }}</span>
                                @endif
                            </div>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($task->users as $u)
                                    <span class="px-2.5 py-1 rounded-full text-white text-xs font-medium" style="background-color: {{ $u->role?->color ?? '#6366f1' }}">
                                        {{ $u->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    {{-- Meals --}}
                    @foreach ($dayEvents['meals'] ?? [] as $meal)
                        <div class="p-4 rounded-lg border-2 border-purple-300 bg-purple-50 dark:bg-purple-900/20 dark:border-purple-700">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-lg">🍽️</span>
                                <span class="font-bold text-neutral-900 dark:text-neutral-100">{{ $meal->meal?->name }}</span>
                                <span class="text-sm text-neutral-500 dark:text-neutral-400">{{ $meal->date_time->format('H:i') }}</span>
                            </div>
                            @if ($meal->notes)
                                <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-2">{{ $meal->notes }}</p>
                            @endif
                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($meal->subscribers as $u)
                                    <span class="px-2.5 py-1 rounded-full text-white text-xs font-medium" style="background-color: {{ $u->role?->color ?? '#6366f1' }}">
                                        {{ $u->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    @if (empty($dayEvents))
                        <div class="text-center py-16 text-neutral-500 dark:text-neutral-400">
                            {{ __('No activities scheduled for this day.') }}
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- Legend --}}
        <div class="rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-zinc-800 p-6">
            <h3 class="font-semibold text-neutral-900 dark:text-neutral-100 mb-3">{{ __('Legend') }}</h3>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                <div class="flex items-center gap-2">
                    <div class="w-5 h-5 rounded border-2 border-indigo-500 bg-indigo-50 dark:bg-indigo-950"></div>
                    <span class="text-sm text-neutral-600 dark:text-neutral-400">{{ __('Today') }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-5 h-5 rounded bg-orange-100 dark:bg-orange-900/40"></div>
                    <span class="text-sm text-neutral-600 dark:text-neutral-400">{{ __('Trip') }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-5 h-5 rounded bg-blue-100 dark:bg-blue-900/40"></div>
                    <span class="text-sm text-neutral-600 dark:text-neutral-400">{{ __('Task') }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-5 h-5 rounded bg-green-100 dark:bg-green-900/40"></div>
                    <span class="text-sm text-neutral-600 dark:text-neutral-400">{{ __('Completed') }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-5 h-5 rounded bg-purple-100 dark:bg-purple-900/40"></div>
                    <span class="text-sm text-neutral-600 dark:text-neutral-400">{{ __('Meal') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Day Details Modal --}}
    @if ($selectedDay && $dayDetails)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" wire:click.self="closeDay">
            <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-2xl max-w-3xl w-full max-h-[80vh] overflow-y-auto">
                {{-- Modal header --}}
                <div class="sticky top-0 bg-white dark:bg-zinc-800 border-b border-neutral-200 dark:border-neutral-700 p-6 flex items-center justify-between z-10">
                    <h3 class="text-xl font-bold text-neutral-900 dark:text-neutral-100">
                        {{ \Carbon\Carbon::parse($selectedDay)->format('l, j F Y') }}
                    </h3>
                    <flux:button variant="ghost" size="sm" wire:click="closeDay" icon="x-mark" />
                </div>

                <div class="p-6 space-y-6">
                    {{-- Trips --}}
                    @if (! empty($dayDetails['trips']))
                        <div>
                            <h4 class="font-bold text-neutral-900 dark:text-neutral-100 mb-3">🏕️ {{ __('Trips') }}</h4>
                            <div class="space-y-3">
                                @foreach ($dayDetails['trips'] as $trip)
                                    <div class="p-4 rounded-lg border-2 border-orange-300 bg-orange-50 dark:bg-orange-900/20 dark:border-orange-700">
                                        <div class="font-semibold text-lg mb-2 text-neutral-900 dark:text-neutral-100">{{ $trip->name }}</div>
                                        @if ($trip->description)
                                            <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-2">{{ $trip->description }}</p>
                                        @endif
                                        <div class="text-sm text-neutral-500 dark:text-neutral-400 mb-2">
                                            {{ $trip->start_date->format('j M') }} → {{ $trip->end_date->format('j M Y') }}
                                        </div>
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach ($trip->users as $u)
                                                <span class="px-2.5 py-1 rounded-full text-white text-xs font-medium" style="background-color: {{ $u->role?->color ?? '#6366f1' }}">
                                                    {{ $u->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Tasks --}}
                    @if (! empty($dayDetails['tasks']))
                        <div>
                            <h4 class="font-bold text-neutral-900 dark:text-neutral-100 mb-3">✓ {{ __('Tasks') }}</h4>
                            <div class="space-y-3">
                                @foreach ($dayDetails['tasks'] as $task)
                                    <div class="p-4 rounded-lg border-2 {{ $task->is_complete ? 'border-green-300 bg-green-50 dark:bg-green-900/20 dark:border-green-700' : 'border-blue-300 bg-blue-50 dark:bg-blue-900/20 dark:border-blue-700' }}">
                                        <div class="font-semibold text-lg mb-1 text-neutral-900 dark:text-neutral-100">{{ $task->title }}</div>
                                        @if ($task->description)
                                            <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-2">{{ $task->description }}</p>
                                        @endif
                                        <div class="flex items-center gap-3 text-sm text-neutral-500 dark:text-neutral-400 mb-2">
                                            @if ($task->taskCategory)
                                                <span>{{ $task->taskCategory->name }}</span>
                                            @endif
                                            @if ($task->locations->isNotEmpty())
                                                <span>📍 {{ $task->locations->pluck('name')->join(', ') }}</span>
                                            @endif
                                            @if ($task->taskPriority)
                                                <span>{{ $task->taskPriority->name }}</span>
                                            @endif
                                        </div>
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach ($task->users as $u)
                                                <span class="px-2.5 py-1 rounded-full text-white text-xs font-medium" style="background-color: {{ $u->role?->color ?? '#6366f1' }}">
                                                    {{ $u->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                        @if ($task->is_complete)
                                            <div class="mt-2 text-green-700 dark:text-green-400 font-bold text-sm">✓ {{ __('Completed') }}</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Meals --}}
                    @if (! empty($dayDetails['meals']))
                        <div>
                            <h4 class="font-bold text-neutral-900 dark:text-neutral-100 mb-3">🍽️ {{ __('Meals') }}</h4>
                            <div class="space-y-3">
                                @foreach ($dayDetails['meals'] as $meal)
                                    <div class="p-4 rounded-lg border-2 border-purple-300 bg-purple-50 dark:bg-purple-900/20 dark:border-purple-700">
                                        <div class="font-semibold text-lg mb-1 text-neutral-900 dark:text-neutral-100">
                                            {{ $meal->meal?->name }}
                                        </div>
                                        <div class="text-sm text-neutral-500 dark:text-neutral-400 mb-2">{{ $meal->date_time->format('H:i') }}</div>
                                        @if ($meal->notes)
                                            <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-2">{{ $meal->notes }}</p>
                                        @endif
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach ($meal->subscribers as $u)
                                                <span class="px-2.5 py-1 rounded-full text-white text-xs font-medium" style="background-color: {{ $u->role?->color ?? '#6366f1' }}">
                                                    {{ $u->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Empty state --}}
                    @if (empty($dayDetails['tasks']) && empty($dayDetails['meals']) && empty($dayDetails['trips']))
                        <div class="text-center py-12 text-neutral-500 dark:text-neutral-400">
                            {{ __('No activities scheduled for this day.') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</section>
