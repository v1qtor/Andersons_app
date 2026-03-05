<div>
    {{-- View Toggle + New Task --}}
    <div class="flex items-center justify-between gap-2 mb-4">
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
        <flux:button size="sm" variant="primary" wire:click="openCreateModal" icon="plus">
            {{ __('New Task') }}
        </flux:button>
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
                                            <span class="font-medium">{{ $task->start_date->format('H:i') }}</span> {{ $task->title }}
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
                $hasAnyAllDay = collect($weekViewData)->filter(fn($d) => !empty($d['allDay']))->isNotEmpty();
                $hours = range(0, 23);
            @endphp
            <div class="overflow-x-auto">
                <div class="min-w-[800px]">
                    {{-- Day headers --}}
                    <div class="grid grid-cols-[48px_repeat(7,1fr)]">
                        <div></div>
                        @foreach ($weekDays as $weekDay)
                            @php
                                $dateStr = $weekDay->format('Y-m-d');
                                $isToday = $dateStr === $today;
                            @endphp
                            <button
                                wire:click="openDay('{{ $dateStr }}')"
                                class="py-1.5 text-center font-semibold text-sm cursor-pointer transition-colors border-l border-neutral-200 dark:border-neutral-700
                                    {{ $isToday
                                        ? 'bg-indigo-600 text-white dark:bg-indigo-500'
                                        : 'bg-neutral-50 text-neutral-700 hover:bg-neutral-100 dark:bg-zinc-700 dark:text-neutral-300 dark:hover:bg-zinc-600' }}"
                            >
                                <div class="text-[10px] uppercase leading-none">{{ $weekDay->format('D') }}</div>
                                <div class="text-base leading-tight {{ $isToday ? 'font-bold' : '' }}">{{ $weekDay->format('j') }}</div>
                            </button>
                        @endforeach
                    </div>

                    {{-- All-day events row --}}
                    @if ($hasAnyAllDay)
                        <div class="grid grid-cols-[48px_repeat(7,1fr)] border-t border-neutral-200 dark:border-neutral-700">
                            <div class="text-[9px] text-neutral-400 dark:text-neutral-500 text-right pr-1 flex items-center justify-end">
                                {{ __('All day') }}
                            </div>
                            @foreach ($weekDays as $weekDay)
                                @php $dateStr = $weekDay->format('Y-m-d'); @endphp
                                <button
                                    wire:click="openDay('{{ $dateStr }}')"
                                    class="border-l border-neutral-200 dark:border-neutral-700 px-0.5 py-0.5 min-h-[24px] cursor-pointer hover:bg-neutral-50 dark:hover:bg-zinc-700/50 transition-colors"
                                >
                                    @foreach ($weekViewData[$dateStr]['allDay'] ?? [] as $item)
                                        <div class="text-[9px] px-1 py-0.5 rounded bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300 truncate">
                                            ⛺ {{ $item['model']->name }}
                                        </div>
                                    @endforeach
                                </button>
                            @endforeach
                        </div>
                    @endif

                    {{-- Time grid --}}
                    <div class="grid grid-cols-[48px_repeat(7,1fr)] border-t border-neutral-200 dark:border-neutral-700">
                        {{-- Hour labels column --}}
                        <div class="relative" style="height: 600px;">
                            @foreach ($hours as $h)
                                <div class="absolute right-0 pr-1 text-[9px] text-neutral-400 dark:text-neutral-500 leading-none select-none"
                                     style="top: {{ round(($h / 24) * 100, 4) }}%; transform: translateY(-50%);">
                                    {{ sprintf('%02d', $h) }}
                                </div>
                            @endforeach
                        </div>

                        {{-- Day columns --}}
                        @foreach ($weekDays as $weekDay)
                            @php
                                $dateStr = $weekDay->format('Y-m-d');
                                $dayData = $weekViewData[$dateStr] ?? ['allDay' => [], 'timed' => []];
                                $isToday = $dateStr === $today;
                            @endphp
                            <div
                                wire:click="openDay('{{ $dateStr }}')"
                                class="relative border-l border-neutral-200 dark:border-neutral-700 cursor-pointer transition-colors
                                    {{ $isToday ? 'bg-indigo-50/40 dark:bg-indigo-950/20' : 'hover:bg-neutral-50/50 dark:hover:bg-zinc-700/20' }}"
                                style="height: 600px;"
                            >
                                {{-- Hour grid lines --}}
                                @foreach ($hours as $h)
                                    <div class="absolute w-full border-t {{ $h % 6 === 0 ? 'border-neutral-200 dark:border-neutral-600' : 'border-neutral-100 dark:border-neutral-700/40' }}"
                                         style="top: {{ round(($h / 24) * 100, 4) }}%;"></div>
                                @endforeach

                                {{-- Current time indicator --}}
                                @if ($isToday)
                                    @php
                                        $now = \Carbon\Carbon::now();
                                        $nowMinutes = $now->hour * 60 + $now->minute;
                                        $nowPercent = round(($nowMinutes / 1440) * 100, 4);
                                    @endphp
                                    <div class="absolute w-full z-20 flex items-center" style="top: {{ $nowPercent }}%;">
                                        <div class="w-2 h-2 rounded-full bg-red-500 -ml-1"></div>
                                        <div class="flex-1 border-t border-red-500"></div>
                                    </div>
                                @endif

                                {{-- Events --}}
                                @foreach ($dayData['timed'] as $event)
                                    @php
                                        $isTask = ($event['type'] ?? 'task') === 'task';
                                        $isMeal = ($event['type'] ?? '') === 'meal';
                                        $model = $event['model'];
                                    @endphp
                                    <div class="absolute z-10 px-px overflow-hidden"
                                         style="top: {{ $event['topPercent'] }}%; height: {{ $event['heightPercent'] }}%; left: {{ $event['leftPercent'] }}%; width: {{ $event['widthPercent'] }}%;">
                                        @if ($isTask)
                                            <div class="h-full rounded-sm px-1 py-0.5 overflow-hidden border-l-2
                                                {{ $model->is_complete
                                                    ? 'bg-green-100 border-green-500 text-green-800 dark:bg-green-900/50 dark:border-green-400 dark:text-green-300'
                                                    : 'bg-blue-100 border-blue-500 text-blue-800 dark:bg-blue-900/50 dark:border-blue-400 dark:text-blue-300' }}">
                                                <div class="text-[9px] font-semibold leading-tight truncate">
                                                    {{ $model->start_date->format('H:i') }} {{ $model->title }}
                                                </div>
                                                @if ($event['heightPercent'] > 3)
                                                    <div class="text-[8px] opacity-70 leading-tight truncate">
                                                        {{ $model->start_date->format('H:i') }}–{{ ($model->end_date ?? $model->start_date->copy()->addHour())->format('H:i') }}
                                                    </div>
                                                @endif
                                            </div>
                                        @elseif ($isMeal)
                                            <div class="h-full rounded-sm px-1 py-0.5 overflow-hidden border-l-2 bg-purple-100 border-purple-500 text-purple-800 dark:bg-purple-900/50 dark:border-purple-400 dark:text-purple-300">
                                                <div class="text-[9px] font-semibold leading-tight truncate">
                                                    {{ $model->date_time->format('H:i') }} 🍽️ {{ $model->meal?->name }}
                                                </div>
                                            </div>
                                        @endif
                                    </div>
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
                <div class="flex justify-end">
                    <flux:button size="sm" variant="primary" wire:click="openCreateModal('{{ $dateStr }}')" icon="plus">
                        {{ __('Add Task') }}
                    </flux:button>
                </div>

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
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <button wire:click="toggleComplete({{ $task->id }})" class="cursor-pointer" title="{{ __('Toggle complete') }}">
                                        <span class="text-lg">{{ $task->is_complete ? '✅' : '⬜' }}</span>
                                    </button>
                                    <span class="font-bold text-neutral-900 dark:text-neutral-100">{{ $task->title }}</span>
                                    <span class="text-sm text-neutral-500 dark:text-neutral-400">{{ $task->start_date->format('H:i') }}@if($task->end_date) – {{ $task->end_date->format('H:i') }}@endif</span>
                                    @if ($task->taskPriority)
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-neutral-200 text-neutral-700 dark:bg-neutral-700 dark:text-neutral-300">{{ $task->taskPriority->name }}</span>
                                    @endif
                                </div>
                                @if ($task->description)
                                    <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-2">{{ $task->description }}</p>
                                @endif
                                <div class="flex items-center gap-3 text-sm text-neutral-500 dark:text-neutral-400">
                                    @if ($task->taskCategory)
                                        <span>{{ $task->taskCategory->name }}</span>
                                    @endif
                                    @if ($task->locations->isNotEmpty())
                                        <span>📍 {{ $task->locations->pluck('name')->join(', ') }}</span>
                                    @endif
                                </div>
                                <div class="flex flex-wrap gap-1.5 mt-2">
                                    @foreach ($task->users as $u)
                                        <span class="px-2.5 py-1 rounded-full text-white text-xs font-medium" style="background-color: {{ $u->role?->color ?? '#6366f1' }}">
                                            {{ $u->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                            <div class="flex items-center gap-1 shrink-0">
                                <flux:button size="sm" variant="ghost" wire:click="openEditModal({{ $task->id }})" icon="pencil-square" />
                                <flux:button size="sm" variant="ghost" wire:click="confirmDelete({{ $task->id }})" icon="trash" class="!text-red-500 hover:!text-red-700" />
                            </div>
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
    <div class="rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-zinc-800 p-6 mt-6">
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

    {{-- Day Details Modal --}}
    @if ($selectedDay && $dayDetails)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
            wire:click.self="closeDay"
            x-data="{
                init() { document.body.style.overflow = 'hidden' },
                destroy() { document.body.style.overflow = '' }
            }"
            @keydown.escape.window="$wire.closeDay()"
        >
            <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-2xl max-w-3xl w-full max-h-[80vh] overflow-y-auto">
                {{-- Modal header --}}
                <div class="sticky top-0 bg-white dark:bg-zinc-800 border-b border-neutral-200 dark:border-neutral-700 p-6 flex items-center justify-between z-10">
                    <h3 class="text-xl font-bold text-neutral-900 dark:text-neutral-100">
                        {{ \Carbon\Carbon::parse($selectedDay)->format('l, j F Y') }}
                    </h3>
                    <div class="flex items-center gap-2">
                        <flux:button size="sm" variant="primary" wire:click="openCreateModal('{{ $selectedDay }}')" icon="plus">
                            {{ __('Add Task') }}
                        </flux:button>
                        <flux:button variant="ghost" size="sm" wire:click="closeDay" icon="x-mark" />
                    </div>
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
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <button wire:click="toggleComplete({{ $task->id }})" class="cursor-pointer" title="{{ __('Toggle complete') }}">
                                                        <span class="text-lg">{{ $task->is_complete ? '✅' : '⬜' }}</span>
                                                    </button>
                                                    <span class="font-semibold text-lg text-neutral-900 dark:text-neutral-100">{{ $task->title }}</span>
                                                </div>
                                                <div class="text-sm text-neutral-500 dark:text-neutral-400 mb-2">
                                                    🕐 {{ $task->start_date->format('j M Y, H:i') }}@if($task->end_date) – {{ $task->end_date->format('j M Y, H:i') }}@endif
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
                                            <div class="flex items-center gap-1 shrink-0">
                                                <flux:button size="sm" variant="ghost" wire:click="openEditModal({{ $task->id }})" icon="pencil-square" />
                                                <flux:button size="sm" variant="ghost" wire:click="confirmDelete({{ $task->id }})" icon="trash" class="!text-red-500 hover:!text-red-700" />
                                            </div>
                                        </div>
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

    {{-- ========== CREATE / EDIT TASK MODAL ========== --}}
    @if (!empty($showTaskModal))
        <x-task-form-modal
            :editing-task-id="$editingTaskId"
            :task-categories="$taskCategories"
            :task-priorities="$taskPriorities"
        />
    @endif

    {{-- ========== DELETE CONFIRMATION MODAL ========== --}}
    @if (!empty($showDeleteModal))
        <x-task-delete-modal />
    @endif
</div>

