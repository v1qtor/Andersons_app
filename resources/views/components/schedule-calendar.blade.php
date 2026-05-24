<div>
    {{-- View Toggle + New Task --}}
    <div class="flex flex-col gap-3 mb-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-2">
            <x-flux.button size="sm" :variant="$view === 'day' ? 'primary' : 'ghost'" wire:click="setView('day')">
                {{ __('Day') }}
            </x-flux.button>
            <x-flux.button size="sm" :variant="$view === 'week' ? 'primary' : 'ghost'" wire:click="setView('week')">
                {{ __('Week') }}
            </x-flux.button>
            <x-flux.button size="sm" :variant="$view === 'month' ? 'primary' : 'ghost'" wire:click="setView('month')">
                {{ __('Month') }}
            </x-flux.button>
        </div>
        <div class="flex items-center gap-2 max-sm:flex-wrap">
            {{-- Ownership sub-filter (only visible when My Tasks is active) --}}
            @if ($showMyTasksOnly)
                <div class="inline-flex rounded-lg border border-neutral-200 dark:border-neutral-700 overflow-hidden">
                    <button
                        wire:click="setMyTaskOwnershipFilter('owner')"
                        class="px-2.5 py-1 text-xs font-medium transition-colors
                            {{ $myTaskOwnershipFilter === 'owner'
                                ? 'bg-indigo-500 text-white dark:bg-indigo-400'
                                : 'bg-white text-neutral-600 hover:bg-neutral-50 dark:bg-zinc-800 dark:text-neutral-400 dark:hover:bg-zinc-700' }}"
                    >
                        {{ __('Owner') }}
                    </button>
                    <button
                        wire:click="setMyTaskOwnershipFilter('not-owned')"
                        class="px-2.5 py-1 text-xs font-medium transition-colors border-l border-neutral-200 dark:border-neutral-700
                            {{ $myTaskOwnershipFilter === 'not-owned'
                                ? 'bg-indigo-500 text-white dark:bg-indigo-400'
                                : 'bg-white text-neutral-600 hover:bg-neutral-50 dark:bg-zinc-800 dark:text-neutral-400 dark:hover:bg-zinc-700' }}"
                    >
                        {{ __('Not Owned') }}
                    </button>
                </div>
            @endif

            <div class="inline-flex rounded-lg border border-neutral-200 dark:border-neutral-700 overflow-hidden">
                <button
                    wire:click="setMyTasksOnly(true)"
                    class="px-3 py-1.5 text-sm font-medium transition-colors
                        {{ $showMyTasksOnly
                            ? 'bg-indigo-600 text-white dark:bg-indigo-500'
                            : 'bg-white text-neutral-700 hover:bg-neutral-50 dark:bg-zinc-800 dark:text-neutral-300 dark:hover:bg-zinc-700' }}"
                >
                    {{ __('My Tasks') }}
                </button>
                <button
                    wire:click="setMyTasksOnly(false)"
                    class="px-3 py-1.5 text-sm font-medium transition-colors border-l border-neutral-200 dark:border-neutral-700
                        {{ ! $showMyTasksOnly
                            ? 'bg-indigo-600 text-white dark:bg-indigo-500'
                            : 'bg-white text-neutral-700 hover:bg-neutral-50 dark:bg-zinc-800 dark:text-neutral-300 dark:hover:bg-zinc-700' }}"
                >
                    {{ __('All Tasks') }}
                </button>
            </div>
            <x-flux.button size="sm" variant="primary" wire:click="openCreateModal" icon="plus">
                {{ __('New Task') }}
            </x-flux.button>
        </div>
    </div>

    {{-- ========== INCOMING COLLABORATION REQUESTS ========== --}}
    @if ($pendingIncomingRequests->isNotEmpty())
        <div class="rounded-xl border border-amber-200 bg-amber-50 dark:border-amber-700 dark:bg-amber-900/20 p-4 mb-6">
            <div class="flex items-center gap-2 mb-3">
                <span class="text-lg">🤝</span>
                <h4 class="font-semibold text-sm text-amber-800 dark:text-amber-300">
                    {{ __('Collaboration Requests') }}
                    <span class="ml-1 inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold rounded-full bg-amber-200 text-amber-800 dark:bg-amber-700 dark:text-amber-100">
                        {{ $pendingIncomingRequests->count() }}
                    </span>
                </h4>
            </div>
            <div class="space-y-2">
                @foreach ($pendingIncomingRequests as $req)
                    <div class="flex items-center justify-between gap-3 p-3 rounded-lg bg-white dark:bg-zinc-800 border border-amber-100 dark:border-amber-800">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100 truncate">
                                {{ $req->task->title }}
                            </p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                {{ __('from') }} <span class="font-medium">{{ $req->requester->name }}</span>
                                · {{ $req->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <x-flux.button size="sm" variant="primary" wire:click="acceptCollaborationRequest({{ $req->id }})">
                                {{ __('Accept') }}
                            </x-flux.button>
                            <x-flux.button size="sm" variant="ghost" wire:click="declineCollaborationRequest({{ $req->id }})">
                                {{ __('Decline') }}
                            </x-flux.button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Navigation + Period Label --}}
    <div class="rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-zinc-800 p-6 mb-6">
        <div class="flex items-center justify-between mb-6">
            <x-flux.button variant="ghost" size="sm" wire:click="previousPeriod" icon="chevron-left" />

            <div class="flex items-center gap-3">
                <h3 class="text-xl font-bold text-neutral-900 dark:text-neutral-100">
                    {{ $this->periodLabel }}
                </h3>
                <x-flux.button variant="ghost" size="sm" wire:click="goToToday">
                    {{ __('Today') }}
                </x-flux.button>
            </div>

            <x-flux.button variant="ghost" size="sm" wire:click="nextPeriod" icon="chevron-right" />
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
                        $normalizedName = strtolower(trim($user->name));
                        $isAndersonFamilyMember = in_array($normalizedName, ['emily anderson', 'james anderson', 'sophie anderson'], true);
                        $roleColor = $isAndersonFamilyMember ? '#948d3b' : ($user->role?->color ?? '#6366f1');
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
                    <x-flux.button variant="ghost" size="sm" wire:click="clearFilters">
                        {{ __('Clear Filters') }}
                    </x-flux.button>
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
                            $dayBirthdays = collect($birthdays)->where('date', $dateStr)->values();
                            $hasAny = $hasTasks || $hasMeals || $hasTrips || $dayBirthdays->isNotEmpty();
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
                                @foreach ($dayBirthdays as $bday)
                                    <div class="text-[10px] leading-tight px-1 py-0.5 rounded bg-pink-100 text-pink-800 dark:bg-pink-900/40 dark:text-pink-300 truncate">
                                        🎂 {{ $bday['name'] }}
                                    </div>
                                @endforeach
                                @if ($hasTrips)
                                    @foreach (array_slice($dayEvents['trips'], 0, 1) as $trip)
                                        <div class="text-[10px] leading-tight px-1 py-0.5 rounded bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300 truncate">
                                            ⛺ {{ $trip->name }}
                                        </div>
                                    @endforeach
                                @endif
                                @if ($hasTasks)
                                    @foreach (array_slice($dayEvents['tasks'], 0, 2) as $task)
                                        @php
                                            $taskOwner = $task->users->firstWhere('pivot.is_owner', true);
                                            $ownerColor = $taskOwner?->role?->color ?? '#6366f1';
                                            // Convert hex to RGB for opacity
                                            $r = hexdec(substr($ownerColor, 1, 2));
                                            $g = hexdec(substr($ownerColor, 3, 2));
                                            $b = hexdec(substr($ownerColor, 5, 2));
                                        @endphp
                                        <div class="text-[10px] leading-tight px-1 py-0.5 rounded truncate"
                                             style="{{ $task->is_complete 
                                                 ? 'background-color: rgb(34 197 94 / 0.2); color: rgb(22 101 52);' 
                                                 : 'background-color: rgba(' . $r . ', ' . $g . ', ' . $b . ', 0.2); color: ' . $ownerColor . ';' }}">
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
                $hasAnyAllDay = collect($weekViewData)->filter(fn($d) => !empty($d['allDay']))->isNotEmpty()
                    || collect($birthdays)->isNotEmpty();
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
                                @php
                                    $dateStr = $weekDay->format('Y-m-d');
                                    $weekDayBirthdays = collect($birthdays)->where('date', $dateStr)->values();
                                @endphp
                                <button
                                    wire:click="openDay('{{ $dateStr }}')"
                                    class="border-l border-neutral-200 dark:border-neutral-700 px-0.5 py-0.5 min-h-[24px] cursor-pointer hover:bg-neutral-50 dark:hover:bg-zinc-700/50 transition-colors"
                                >
                                    @foreach ($weekViewData[$dateStr]['allDay'] ?? [] as $item)
                                        <div class="text-[9px] px-1 py-0.5 rounded bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300 truncate">
                                            ⛺ {{ $item['model']->name }}
                                        </div>
                                    @endforeach
                                    @foreach ($weekDayBirthdays as $bday)
                                        <div class="text-[9px] px-1 py-0.5 rounded bg-pink-100 text-pink-800 dark:bg-pink-900/40 dark:text-pink-300 truncate">
                                            🎂 {{ $bday['name'] }}
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
                                        
                                        if ($isTask) {
                                            $taskOwner = $model->users->firstWhere('pivot.is_owner', true);
                                            $ownerColor = $taskOwner?->role?->color ?? '#6366f1';
                                            $r = hexdec(substr($ownerColor, 1, 2));
                                            $g = hexdec(substr($ownerColor, 3, 2));
                                            $b = hexdec(substr($ownerColor, 5, 2));
                                        }
                                    @endphp
                                    <div class="absolute z-10 px-px overflow-hidden"
                                         style="top: {{ $event['topPercent'] }}%; height: {{ $event['heightPercent'] }}%; left: {{ $event['leftPercent'] }}%; width: {{ $event['widthPercent'] }}%;">
                                        @if ($isTask)
                                            <div class="h-full rounded-sm px-1 py-0.5 overflow-hidden border-l-2"
                                                 style="{{ $model->is_complete
                                                     ? 'background-color: rgb(34 197 94 / 0.2); border-color: rgb(34 197 94); color: rgb(22 101 52);'
                                                     : 'background-color: rgba(' . $r . ', ' . $g . ', ' . $b . ', 0.2); border-color: ' . $ownerColor . '; color: ' . $ownerColor . ';' }}">
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
                $dayBirthdaysView = collect($birthdays)->where('date', $dateStr)->values();
            @endphp
            <div class="space-y-4">
                <div class="flex justify-end">
                    <x-flux.button size="sm" variant="primary" wire:click="openCreateModal('{{ $dateStr }}')" icon="plus">
                        {{ __('Add Task') }}
                    </x-flux.button>
                </div>

                {{-- Birthdays --}}
                @foreach ($dayBirthdaysView as $bday)
                    <div class="p-4 rounded-lg border-2 border-pink-300 bg-pink-50 dark:bg-pink-900/20 dark:border-pink-700">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">🎂</span>
                            <span class="font-bold text-neutral-900 dark:text-neutral-100">{{ $bday['name'] }}</span>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-pink-200 text-pink-800 dark:bg-pink-800 dark:text-pink-200">{{ __('Birthday') }}</span>
                        </div>
                        @if ($bday['notes'])
                            <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">{{ $bday['notes'] }}</p>
                        @endif
                    </div>
                @endforeach

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
                    @php
                        $taskOwner = $task->users->firstWhere('pivot.is_owner', true);
                        $ownerColor = $taskOwner?->role?->color ?? '#6366f1';
                        $r = hexdec(substr($ownerColor, 1, 2));
                        $g = hexdec(substr($ownerColor, 3, 2));
                        $b = hexdec(substr($ownerColor, 5, 2));
                    @endphp
                    <div class="p-4 rounded-lg border-2"
                         style="{{ $task->is_complete 
                             ? 'border-color: rgb(34 197 94); background-color: rgb(34 197 94 / 0.1);' 
                             : 'border-color: ' . $ownerColor . '; background-color: rgba(' . $r . ', ' . $g . ', ' . $b . ', 0.1);' }}">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
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
                            @if ($isAdmin || $task->users->where('id', auth()->id())->first()?->pivot?->is_owner)
                                <div class="flex items-center gap-1 shrink-0">
                                    <x-flux.button size="sm" variant="ghost" wire:click="openEditModal({{ $task->id }})" icon="pencil-square" />
                                    <x-flux.button size="sm" variant="ghost" wire:click="confirmDelete({{ $task->id }})" icon="trash" class="!text-red-500 hover:!text-red-700" />
                                </div>
                            @endif
                        </div>
                        <div class="flex justify-end mt-3">
                            @if ($task->is_complete)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300 text-sm font-semibold">
                                    ✓ {{ __('Completed') }}
                                </span>
                            @elseif ($isAdmin || $task->users->contains('id', auth()->id()))
                                <x-flux.button size="sm" variant="primary" wire:click="markComplete({{ $task->id }})" icon="check">
                                    {{ __('Mark Complete') }}
                                </x-flux.button>
                            @endif
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
        <x-ui.detail-modal
            :show="true"
            :title="\Carbon\Carbon::parse($selectedDay)->format('l, j F Y')"
            maxWidth="max-w-3xl"
            closeAction="closeDay"
            escapeAction="$wire.closeDay()"
            :lockBodyScroll="true"
            panelClass="rounded-2xl shadow-2xl max-h-[80vh]"
            titleClass="text-xl font-bold text-neutral-900 dark:text-neutral-100"
            headerClass="z-10"
        >
            <x-slot:headerActions>
                <x-flux.button size="sm" variant="primary" wire:click="openCreateModal('{{ $selectedDay }}')" icon="plus">
                    {{ __('Add Task') }}
                </x-flux.button>
            </x-slot:headerActions>

            <div class="space-y-6">
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
                                    @php
                                        $taskOwner = $task->users->firstWhere('pivot.is_owner', true);
                                        $ownerColor = $taskOwner?->role?->color ?? '#6366f1';
                                        $r = hexdec(substr($ownerColor, 1, 2));
                                        $g = hexdec(substr($ownerColor, 3, 2));
                                        $b = hexdec(substr($ownerColor, 5, 2));
                                    @endphp
                                    <div class="p-4 rounded-lg border-2"
                                         style="{{ $task->is_complete 
                                             ? 'border-color: rgb(34 197 94); background-color: rgb(34 197 94 / 0.1);' 
                                             : 'border-color: ' . $ownerColor . '; background-color: rgba(' . $r . ', ' . $g . ', ' . $b . ', 0.1);' }}">
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <span class="font-semibold text-lg text-neutral-900 dark:text-neutral-100">{{ $task->title }}</span>
                                                </div>
                                                <div class="text-sm text-neutral-500 dark:text-neutral-400 mb-2">
                                                    � {{ $task->start_date->format('j M Y, H:i') }}@if($task->end_date) – {{ $task->end_date->format('j M Y, H:i') }}@endif
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
                                            </div>
                                            @if ($isAdmin || $task->users->where('id', auth()->id())->first()?->pivot?->is_owner)
                                                <div class="flex items-center gap-1 shrink-0">
                                                    <x-flux.button size="sm" variant="ghost" wire:click="openEditModal({{ $task->id }})" icon="pencil-square" />
                                                    <x-flux.button size="sm" variant="ghost" wire:click="confirmDelete({{ $task->id }})" icon="trash" class="!text-red-500 hover:!text-red-700" />
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex justify-end mt-3">
                                            @if ($task->is_complete)
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300 text-sm font-semibold">
                                                    ✓ {{ __('Completed') }}
                                                </span>
                                            @elseif ($isAdmin || $task->users->contains('id', auth()->id()))
                                                <x-flux.button size="sm" variant="primary" wire:click="markComplete({{ $task->id }})" icon="check">
                                                    {{ __('Mark Complete') }}
                                                </x-flux.button>
                                            @endif
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
        </x-ui.detail-modal>
    @endif

    {{-- ========== CREATE / EDIT TASK MODAL ========== --}}
    @if (!empty($showTaskModal))
        <x-task-form-modal
            :editing-task-id="$editingTaskId"
            :task-categories="$taskCategories"
            :task-priorities="$taskPriorities"
            :all-users="$allUsers"
            :is-admin="$isAdmin"
            :task-owner-id="$taskOwnerId"
            :assigned-user-ids="$assignedUserIds"
            :locations="$locations"
            :selected-location-ids="$selectedLocationIds"
            :collaboration-user-ids="$collaborationUserIds"
            :pending-outgoing-user-ids="$pendingOutgoingUserIds"
            :unavailable-user-ids="$unavailableUserIds ?? []"
        />
    @endif

    {{-- ========== DELETE CONFIRMATION MODAL ========== --}}
    @if (!empty($showDeleteModal))
        <x-task-delete-modal />
    @endif

    {{-- ========== PRINT MODAL ========== --}}
    @if ($showPrintModal)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
            wire:click.self="closePrintModal"
            x-data="{
                init() { document.body.style.overflow = 'hidden' },
                destroy() { document.body.style.overflow = '' },
                doPrint() {
                    const el = document.getElementById('printArea');
                    if (!el) return;
                    const css = `
                        @page { size: landscape; margin: 1.5cm 1cm; @top-center { content: none; } @bottom-center { content: none; } }
                        * { box-sizing: border-box; margin: 0; padding: 0; }
                        body { font-family: Arial, sans-serif; font-size: 10pt; color: #111; background: white; }
                        h2 { font-size: 16pt; font-weight: 700; margin-bottom: 4px; }
                        p { font-size: 9pt; color: #555; margin-bottom: 16px; }
                        div { background: white !important; border: none !important; border-radius: 0 !important; padding: 0 !important; }
                        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
                        thead { display: table-header-group; }
                        th:nth-child(1), td:nth-child(1) { width: 8%; }
                        th:nth-child(2), td:nth-child(2) { width: 8%; }
                        th:nth-child(3), td:nth-child(3) { width: 20%; }
                        th:nth-child(4), td:nth-child(4) { width: 9%; }
                        th:nth-child(5), td:nth-child(5) { width: 7%; }
                        th:nth-child(6), td:nth-child(6) { width: 10%; }
                        th:nth-child(7), td:nth-child(7) { width: 14%; }
                        th:nth-child(8), td:nth-child(8) { width: 10%; }
                        th:nth-child(9), td:nth-child(9) { width: 7%; }
                        th { font-size: 8pt; font-weight: 700; text-align: left; padding: 5px 6px; background: #f9fafb !important; border-bottom: 2px solid #d1d5db !important; color: #111 !important; }
                        td { font-size: 8pt; padding: 5px 6px; border-bottom: 1px solid #e5e7eb !important; vertical-align: top; word-wrap: break-word; color: #111 !important; background: white !important; }
                        tr { page-break-inside: avoid; }
                        span { display: inline-flex; align-items: center; padding: 2px 6px; border-radius: 9999px; font-size: 7pt; font-weight: 600; }
                        .bg-green-100 { background: #dcfce7 !important; color: #166534 !important; }
                        .bg-neutral-100 { background: #f3f4f6 !important; color: #374151 !important; }
                    `;
                    const win = window.open('', '_blank', 'width=1200,height=900');
                    if (!win) { alert('Lütfen tarayıcınızda pop-up izni verin.'); return; }
                    win.document.write('<html><head><meta charset=utf-8><title></title><style>' + css + '</style></head><body>' + el.innerHTML + '</body></html>');
                    win.document.close();
                    win.focus();
                    setTimeout(() => { win.print(); win.close(); }, 500);
                }
            }"
            @keydown.escape.window="$wire.closePrintModal()"
        >
            <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                {{-- Modal header --}}
                <div class="sticky top-0 bg-white dark:bg-zinc-800 border-b border-neutral-200 dark:border-neutral-700 p-6 flex items-center justify-between z-10">
                    <h3 class="text-xl font-bold text-neutral-900 dark:text-neutral-100">
                        {{ __('Print Schedule') }}
                    </h3>
                    <flux:button variant="ghost" size="sm" wire:click="closePrintModal" icon="x-mark" />
                </div>

                <div class="p-6">
                    {{-- Print Options --}}
                    <div class="mb-6 space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-2">
                                {{ __('Scope') }}
                            </label>
                            <div class="inline-flex rounded-lg border border-neutral-200 dark:border-neutral-700 overflow-hidden">
                                <button
                                    wire:click="setPrintScope('allTasks')"
                                    class="px-4 py-2 text-sm font-medium transition-colors
                                        {{ $printScope === 'allTasks'
                                            ? 'bg-indigo-600 text-white dark:bg-indigo-500'
                                            : 'bg-white text-neutral-700 hover:bg-neutral-50 dark:bg-zinc-800 dark:text-neutral-300 dark:hover:bg-zinc-700' }}"
                                >
                                    {{ __('All Tasks') }}
                                </button>
                                <button
                                    wire:click="setPrintScope('myTasks')"
                                    class="px-4 py-2 text-sm font-medium transition-colors border-l border-neutral-200 dark:border-neutral-700
                                        {{ $printScope === 'myTasks'
                                            ? 'bg-indigo-600 text-white dark:bg-indigo-500'
                                            : 'bg-white text-neutral-700 hover:bg-neutral-50 dark:bg-zinc-800 dark:text-neutral-300 dark:hover:bg-zinc-700' }}"
                                >
                                    {{ __('My Tasks') }}
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-2">
                                {{ __('Period') }}
                            </label>
                            <div class="flex flex-wrap items-center gap-3">
                                <div class="inline-flex rounded-lg border border-neutral-200 dark:border-neutral-700 overflow-hidden">
                                    <button
                                        wire:click="setPrintPeriod('daily')"
                                        class="px-4 py-2 text-sm font-medium transition-colors
                                            {{ $printPeriod === 'daily'
                                                ? 'bg-indigo-600 text-white dark:bg-indigo-500'
                                                : 'bg-white text-neutral-700 hover:bg-neutral-50 dark:bg-zinc-800 dark:text-neutral-300 dark:hover:bg-zinc-700' }}"
                                    >
                                        {{ __('Daily') }}
                                    </button>
                                    <button
                                        wire:click="setPrintPeriod('weekly')"
                                        class="px-4 py-2 text-sm font-medium transition-colors border-l border-neutral-200 dark:border-neutral-700
                                            {{ $printPeriod === 'weekly'
                                                ? 'bg-indigo-600 text-white dark:bg-indigo-500'
                                                : 'bg-white text-neutral-700 hover:bg-neutral-50 dark:bg-zinc-800 dark:text-neutral-300 dark:hover:bg-zinc-700' }}"
                                    >
                                        {{ __('Weekly') }}
                                    </button>
                                    <button
                                        wire:click="setPrintPeriod('monthly')"
                                        class="px-4 py-2 text-sm font-medium transition-colors border-l border-neutral-200 dark:border-neutral-700
                                            {{ $printPeriod === 'monthly'
                                                ? 'bg-indigo-600 text-white dark:bg-indigo-500'
                                                : 'bg-white text-neutral-700 hover:bg-neutral-50 dark:bg-zinc-800 dark:text-neutral-300 dark:hover:bg-zinc-700' }}"
                                    >
                                        {{ __('Monthly') }}
                                    </button>
                                    <button
                                        wire:click="setPrintPeriod('custom')"
                                        class="px-4 py-2 text-sm font-medium transition-colors border-l border-neutral-200 dark:border-neutral-700
                                            {{ $printPeriod === 'custom'
                                                ? 'bg-indigo-600 text-white dark:bg-indigo-500'
                                                : 'bg-white text-neutral-700 hover:bg-neutral-50 dark:bg-zinc-800 dark:text-neutral-300 dark:hover:bg-zinc-700' }}"
                                    >
                                        {{ __('Custom') }}
                                    </button>
                                </div>

                                {{-- Custom date range pickers --}}
                                @if ($printPeriod === 'custom')
                                    <div class="flex items-center gap-2">
                                        <input
                                            type="date"
                                            wire:model.live="printCustomStart"
                                            class="px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-zinc-800 text-neutral-900 dark:text-neutral-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                                        >
                                        <span class="text-neutral-400 dark:text-neutral-500 text-sm font-medium">→</span>
                                        <input
                                            type="date"
                                            wire:model.live="printCustomEnd"
                                            class="px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-zinc-800 text-neutral-900 dark:text-neutral-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                                        >
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Custom range warning --}}
                    @if ($printPeriod === 'custom' && (! $printCustomStart || ! $printCustomEnd))
                        <div class="mb-4 p-3 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 text-sm text-amber-700 dark:text-amber-300">
                            {{ __('Please select both a start and end date to generate the preview.') }}
                        </div>
                    @endif

                    {{-- Print Preview --}}
                    @if ($printData && $printData['rangeStart'])
                        <div class="border border-neutral-200 dark:border-neutral-700 rounded-lg p-6 bg-neutral-50 dark:bg-zinc-900" id="printArea">
                            <div class="mb-6">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <h2 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100 mb-2">
                                            {{ __('Schedule') }}
                                        </h2>
                                        <p class="text-sm text-neutral-600 dark:text-neutral-400">
                                            {{ $printData['rangeStart']->format('j M Y') }} - {{ $printData['rangeEnd']->format('j M Y') }}
                                            · {{ $printData['scope'] === 'myTasks' ? __('My Tasks') : __('All Tasks') }}
                                        </p>
                                    </div>
                                    <p class="text-xs text-neutral-400 dark:text-neutral-500 text-right">
                                        {{ __('Printed') }}: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                            </div>

                            @if ($printData['tasks']->isEmpty())
                                <div class="text-center py-12 text-neutral-500 dark:text-neutral-400">
                                    {{ __('No tasks found for the selected period.') }}
                                </div>
                            @else
                                <div class="overflow-x-auto">
                                    <table class="w-full border-collapse text-xs">
                                        <thead>
                                            <tr class="border-b-2 border-neutral-300 dark:border-neutral-600">
                                                <th class="text-left py-2 px-1.5 text-[10px] font-bold text-neutral-900 dark:text-neutral-100">{{ __('Date') }}</th>
                                                <th class="text-left py-2 px-1.5 text-[10px] font-bold text-neutral-900 dark:text-neutral-100">{{ __('Time') }}</th>
                                                <th class="text-left py-2 px-1.5 text-[10px] font-bold text-neutral-900 dark:text-neutral-100">{{ __('Task') }}</th>
                                                <th class="text-left py-2 px-1.5 text-[10px] font-bold text-neutral-900 dark:text-neutral-100">{{ __('Category') }}</th>
                                                <th class="text-left py-2 px-1.5 text-[10px] font-bold text-neutral-900 dark:text-neutral-100">{{ __('Priority') }}</th>
                                                <th class="text-left py-2 px-1.5 text-[10px] font-bold text-neutral-900 dark:text-neutral-100">{{ __('Owner') }}</th>
                                                <th class="text-left py-2 px-1.5 text-[10px] font-bold text-neutral-900 dark:text-neutral-100">{{ __('Assigned To') }}</th>
                                                <th class="text-left py-2 px-1.5 text-[10px] font-bold text-neutral-900 dark:text-neutral-100">{{ __('Location') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($printData['tasks'] as $task)
                                                @php
                                                    $taskOwner = $task->users->firstWhere('pivot.is_owner', true);
                                                @endphp
                                                <tr class="border-b border-neutral-200 dark:border-neutral-700">
                                                    <td class="py-1.5 px-1.5 text-[10px] text-neutral-900 dark:text-neutral-100 whitespace-nowrap">
                                                        {{ $task->start_date->format('j M Y') }}
                                                    </td>
                                                    <td class="py-1.5 px-1.5 text-[10px] text-neutral-900 dark:text-neutral-100 whitespace-nowrap">
                                                        {{ $task->start_date->format('H:i') }}@if($task->end_date)-{{ $task->end_date->format('H:i') }}@endif
                                                    </td>
                                                    <td class="py-1.5 px-1.5 text-[10px] text-neutral-900 dark:text-neutral-100">
                                                        <div class="font-semibold">{{ $task->title }}</div>
                                                        @if ($task->description)
                                                            <div class="text-[9px] text-neutral-600 dark:text-neutral-400 mt-0.5">{{ Str::limit($task->description, 50) }}</div>
                                                        @endif
                                                    </td>
                                                    <td class="py-1.5 px-1.5 text-[10px] text-neutral-700 dark:text-neutral-300 whitespace-nowrap">
                                                        {{ $task->taskCategory?->name ?? '-' }}
                                                    </td>
                                                    <td class="py-1.5 px-1.5 text-[10px] text-neutral-700 dark:text-neutral-300 whitespace-nowrap">
                                                        {{ $task->taskPriority?->name ?? '-' }}
                                                    </td>
                                                    <td class="py-1.5 px-1.5 text-[10px] text-neutral-700 dark:text-neutral-300 whitespace-nowrap">
                                                        {{ $taskOwner?->name ?? '-' }}
                                                    </td>
                                                    <td class="py-1.5 px-1.5 text-[10px] text-neutral-700 dark:text-neutral-300">
                                                        @foreach ($task->users as $index => $user)
                                                            {{ $user->name }}@if(!$loop->last),@endif<br>
                                                        @endforeach
                                                    </td>
                                                    <td class="py-1.5 px-1.5 text-[10px] text-neutral-700 dark:text-neutral-300">
                                                        @if($task->locations->isNotEmpty())
                                                            @foreach ($task->locations as $location)
                                                                {{ $location->name }}@if(!$loop->last),@endif<br>
                                                            @endforeach
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                            @endif
                        </div>
                    @endif

                    {{-- Action Buttons --}}
                    <div class="flex justify-end gap-3 mt-6">
                        <flux:button variant="ghost" wire:click="closePrintModal">
                            {{ __('Cancel') }}
                        </flux:button>
                        <flux:button variant="primary" icon="printer" x-on:click="doPrint()">
                            {{ __('Print') }}
                        </flux:button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

