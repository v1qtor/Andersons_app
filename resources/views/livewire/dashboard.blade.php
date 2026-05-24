<div>
    <div class="flex w-full flex-col gap-6">
        
        {{-- Header Area --}}
        <div>
            <h1 class="text-3xl font-medium tracking-tight text-neutral-900 dark:text-white">Dashboard</h1>
            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                Welcome back, {{ explode(' ', auth()->user()->name)[0] }}! Here's what's happening today.
            </p>
        </div>

        {{-- Top Cards Row --}}
        <div class="grid grid-cols-1 md:grid-cols-3 {{ $isAdmin ? 'lg:grid-cols-4' : '' }} gap-6">
            {{-- Card 1: Tasks Today --}}
            <div class="bg-white dark:bg-neutral-900/50 border border-neutral-200 dark:border-neutral-700/50 rounded-2xl p-5 flex items-center justify-between shadow-[0_2px_10px_-3px_rgba(0,0,0,0.05)]">
                <div>
                    <h3 class="text-lg text-neutral-600 dark:text-neutral-400">Tasks Today</h3>
                    <p class="text-3xl mt-1 text-neutral-800 dark:text-neutral-200">{{ $tasksCount }}</p>
                </div>
                <div class="bg-[#1e88e5] rounded-full p-2.5 text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                    </svg>
                </div>
            </div>

            {{-- Card 2: Upcoming Trips --}}
            <div class="bg-white dark:bg-neutral-900/50 border border-neutral-200 dark:border-neutral-700/50 rounded-2xl p-5 flex items-center justify-between shadow-[0_2px_10px_-3px_rgba(0,0,0,0.05)]">
                <div>
                    <h3 class="text-lg text-neutral-600 dark:text-neutral-400">Upcoming Trips</h3>
                    <p class="text-3xl mt-1 text-neutral-800 dark:text-neutral-200">{{ $totalTripsCount }}</p>
                </div>
                <div class="bg-[#d946ef] rounded-full p-2.5 text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                    </svg>
                </div>
            </div>

            {{-- Card 3: Dinner Plans --}}
            <div class="bg-white dark:bg-neutral-900/50 border border-neutral-200 dark:border-neutral-700/50 rounded-2xl p-5 flex items-center justify-between shadow-[0_2px_10px_-3px_rgba(0,0,0,0.05)]">
                <div>
                    <h3 class="text-lg text-neutral-600 dark:text-neutral-400">Dinner Plans</h3>
                    <p class="text-3xl mt-1 text-neutral-800 dark:text-neutral-200">{{ $totalDinnerCount }}</p>
                </div>
                <div class="bg-[#ea580c] rounded-full p-2.5 text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-6">
                      <path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2" />
                      <path d="M7 2v20" />
                      <path d="M21 15V2v0a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7" />
                    </svg>
                </div>
            </div>

            @if($isAdmin)
                {{-- Card 4: Upcoming Unavailability --}}
                <div class="bg-white dark:bg-neutral-900/50 border border-neutral-200 dark:border-neutral-700/50 rounded-2xl p-5 flex items-center justify-between shadow-[0_2px_10px_-3px_rgba(0,0,0,0.05)]">
                    <div>
                        <h3 class="text-lg text-neutral-600 dark:text-neutral-400">Upcoming Unavailability</h3>
                        <p class="text-3xl mt-1 text-neutral-800 dark:text-neutral-200">{{ $upcomingUnavailabilityCount }}</p>
                    </div>
                    <div class="bg-[#ef4444] rounded-full p-2.5 text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4.5m0 3.75h.008v.008H12v-.008Zm-7.071 2.121A9 9 0 1 1 19.071 5.378 9 9 0 0 1 4.929 19.371Z" />
                        </svg>
                    </div>
                </div>
            @endif
        </div>

        {{-- Main Content 2 Columns --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start pb-6">
            
            {{-- Left Column: Today's Tasks --}}
            <div class="lg:col-span-7 bg-white dark:bg-neutral-900/50 border border-neutral-200 dark:border-neutral-700/50 rounded-2xl p-6 shadow-[0_2px_10px_-4px_rgba(0,0,0,0.05)]">
                <div class="flex items-center justify-between gap-4 flex-wrap mb-6">
                    <div class="flex items-center gap-4">
                        <h2 class="text-2xl font-medium text-neutral-800 dark:text-neutral-200">Today's Tasks</h2>
                        <div class="text-lg text-neutral-800 dark:text-neutral-200">{{ now()->format('n/j/Y') }}</div>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <select wire:model.live="priorityFilter" class="bg-white border border-neutral-200 text-neutral-700 text-sm rounded-lg px-3 py-1.5 focus:ring-blue-500 focus:border-blue-500 outline-none shadow-sm dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-200">
                            <option value="">Priority</option>
                            @foreach($priorities as $priority)
                                <option value="{{ $priority->name }}">{{ $priority->name }}</option>
                            @endforeach
                        </select>

                        <select wire:model.live="timeFilter" class="bg-white border border-neutral-200 text-neutral-700 text-sm rounded-lg px-3 py-1.5 focus:ring-blue-500 focus:border-blue-500 outline-none shadow-sm dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-200">
                            <option value="">Time</option>
                            <option value="morning">Morning</option>
                            <option value="afternoon">Afternoon</option>
                            <option value="evening">Evening</option>
                        </select>

                        <div class="bg-blue-50/50 dark:bg-blue-900/30 text-blue-500 dark:text-blue-400 text-xs px-3 py-1.5 rounded-md font-medium ml-1">
                            {{ $completedCount }}/{{ $tasksCount }} Complete
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-4">
                    @forelse($todayTasks as $index => $task)
                        @php
                            $owner = $task->users->where('pivot.is_owner', true)->first() ?? $task->users->first();
                            $baseOwnerColor = $owner?->role?->color ?? '#6366f1';
                            $ownerColor = preg_match('/^#[0-9A-Fa-f]{6}$/', $baseOwnerColor) ? $baseOwnerColor : '#6366f1';
                            $taskBgColor = $task->is_complete ? '#dcfce7' : $ownerColor . '1F';
                            $taskBorderColor = $task->is_complete ? '#4ade80' : $ownerColor;
                            $indicatorColor = $task->is_complete ? '#22c55e' : $ownerColor;
                        @endphp
                        <div class="rounded-2xl border p-4 relative group" style="background-color: {{ $taskBgColor }}; border-color: {{ $taskBorderColor }};">
                            <div class="flex justify-between items-center gap-4 max-sm:flex-col max-sm:items-stretch">
                                <div class="flex gap-4 max-sm:min-w-0">
                                    <div class="pt-0.5 shrink-0">
                                        @if($task->is_complete)
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-[26px] mt-1" style="color: {{ $indicatorColor }};">
                                                <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd" />
                                            </svg>
                                        @else
                                            @if($canManageTasks)
                                                <button wire:click="markTaskAsDone({{ $task->id }})" class="size-[26px] mt-1 rounded-full border-2 bg-transparent hover:bg-slate-100 dark:hover:bg-neutral-800 transition-colors cursor-pointer" style="border-color: {{ $indicatorColor }};"></button>
                                            @else
                                                <div class="size-[26px] mt-1 rounded-full border-2 bg-transparent" style="border-color: {{ $indicatorColor }};"></div>
                                            @endif
                                        @endif
                                    </div>
                                    <div class="max-sm:min-w-0">
                                        <h4 class="text-[20px] text-neutral-800 dark:text-neutral-200 break-words">{{ $task->title }}</h4>
                                        <div class="flex items-center gap-6 mt-1 text-[15px] text-neutral-600 dark:text-neutral-400 max-sm:flex-wrap max-sm:gap-y-2">
                                            <div class="flex items-center gap-1.5">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                </svg>
                                                {{ $task->start_date?->format('H:i') ?? '08:00' }} - {{ $task->end_date?->format('H:i') ?? '09:00' }}
                                            </div>
                                            @if($task->locations->isNotEmpty())
                                            <div class="flex items-center gap-1.5">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                  <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                                </svg>
                                                {{ $task->locations->first()->name }}
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex items-center pr-2 max-sm:flex-col max-sm:items-stretch max-sm:gap-3 max-sm:pr-0">
                                    <div class="flex flex-col items-center justify-center gap-1.5 mr-4 mt-0.5 max-sm:flex-row max-sm:items-start max-sm:gap-2 max-sm:mr-0">
                                        @if($owner)
                                            <div class="text-white text-[12px] px-4 py-0.5 rounded-full whitespace-nowrap" style="background-color: {{ $ownerColor }};">
                                                {{ explode(' ', $owner->name)[0] }}
                                            </div>
                                        @endif
                                        @if($task->taskPriority)
                                            <div class="bg-cyan-50 dark:bg-cyan-900/30 text-[#0bcbb5] border border-cyan-200 dark:border-cyan-800 text-[11px] px-4 py-[1px] rounded-full lowercase">
                                                {{ $task->taskPriority->name }}
                                            </div>
                                        @elseif($index < 3)
                                            <div class="bg-cyan-50 dark:bg-cyan-900/30 text-[#0bcbb5] border border-cyan-200 dark:border-cyan-800 text-[11px] px-4 py-[1px] rounded-full lowercase">
                                                medium
                                            </div>
                                        @endif
                                    </div>
                                    
                                    @if($canManageTasks)
                                        @if(!$task->is_complete)
                                            <button wire:click="markTaskAsDone({{ $task->id }})" class="bg-gradient-to-r from-blue-600 to-[#0ba5cc] hover:from-blue-700 hover:to-[#0896ba] text-white text-[17px] px-6 py-2.5 rounded-xl font-medium transition-colors shadow-sm ml-2 max-sm:w-full max-sm:ml-0 max-sm:text-[16px] max-sm:px-5 max-sm:py-2">
                                                Done
                                            </button>
                                        @else
                                            <button disabled class="bg-neutral-200 dark:bg-neutral-800 text-neutral-400 dark:text-neutral-600 text-[17px] px-6 py-2.5 rounded-xl font-medium cursor-not-allowed ml-2 shadow-sm max-sm:w-full max-sm:ml-0 max-sm:text-[16px] max-sm:px-5 max-sm:py-2">
                                                Done
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-neutral-500">
                            No tasks scheduled for today.
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Right Column --}}
            <div class="lg:col-span-5 flex flex-col gap-6">
                
                {{-- Upcoming Dinners --}}
                <livewire:meals.upcoming-dinners />

                {{-- Upcoming Trips --}}
                <div class="bg-[#fcfbfe] dark:bg-neutral-900/50 border border-neutral-100 dark:border-neutral-700/50 rounded-2xl p-6 shadow-[0_2px_10px_-4px_rgba(0,0,0,0.05)]">
                    <h2 class="text-2xl font-medium text-neutral-800 dark:text-neutral-200 mb-4">Upcoming Trips</h2>
                    
                    <div class="flex flex-col gap-3">
                        @forelse($upcomingTrips as $trip)
                            <div class="bg-white dark:bg-neutral-800 border border-neutral-100 dark:border-neutral-700/50 rounded-xl p-4 flex items-center justify-between shadow-sm">
                                <div class="flex items-center gap-4">
                                    <div class="bg-[#f2e6fb] dark:bg-purple-900/20 text-[#d87aff] size-[38px] rounded-full flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="text-[16px] font-normal text-neutral-800 dark:text-neutral-200">{{ $trip->name }}</h4>
                                        <div class="text-sm text-neutral-500 mt-0.5">
                                            {{ $trip->start_date->format('M j, Y') }} • {{ $trip->end_date->format('M j, Y') }}
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1 text-neutral-500 text-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-[14px]">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                    </svg>
                                    {{ $trip->checkpoints->count() }} checkpoints
                                </div>
                            </div>
                        @empty
                            <div class="text-neutral-500 text-center py-4 bg-white dark:bg-neutral-800 border border-neutral-100 dark:border-neutral-700/50 rounded-xl">
                                No upcoming trips.
                            </div>
                        @endforelse
                    </div>

                    @if($upcomingTrips->hasPages())
                        <div class="mt-4">
                            {{ $upcomingTrips->onEachSide(1)->links('livewire::simple-tailwind') }}
                        </div>
                    @endif
                </div>

                    <div class="mt-6 rounded-2xl border border-[#fecdd3] dark:border-[#881337] bg-[#fff1f2] dark:bg-[#4c0519] p-5 sm:p-6 shadow-[0_2px_10px_-4px_rgba(0,0,0,0.05)]">
                        <div class="flex items-center justify-between gap-3 mb-4">
                            <div>
                                <h3 class="text-xl font-medium text-neutral-800 dark:text-neutral-100">Upcoming Unavailability</h3>
                                <p class="text-sm text-neutral-500 dark:text-neutral-400">Unavailability for the next 7 days.</p>
                            </div>
                            <div class="bg-[#ef4444] rounded-full p-2.5 text-white shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4.5m0 3.75h.008v.008H12v-.008Zm-7.071 2.121A9 9 0 1 1 19.071 5.378 9 9 0 0 1 4.929 19.371Z" />
                                </svg>
                            </div>
                        </div>

                        <div class="flex flex-col gap-2">
                            @forelse($upcomingUnavailability as $unavailability)
                                <div class="rounded-2xl border border-[#fecdd3] dark:border-[#881337] bg-white/80 dark:bg-neutral-900/60 p-4">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                        <div class="flex items-center gap-4 min-w-0 overflow-auto">
                                            <div class="bg-[#ef4444] rounded-full p-2.5 text-white shrink-0">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                </svg>
                                            </div>
                                            <div class="min-w-0">
                                                <h4 class="text-base font-medium text-neutral-800 dark:text-neutral-100 truncate">
                                                    {{ $unavailability->user->name }}
                                                </h4>
                                                <p class="text-sm text-neutral-500 dark:text-neutral-400 break-words">
                                                    {{ $unavailability->start_date->format('M j, Y g:i A') }} - {{ $unavailability->end_date->format('M j, Y g:i A') }}
                                                </p>
                                            </div>
                                        </div>

                                        @if($unavailability->description)
                                            <div class="text-sm text-neutral-600 dark:text-neutral-300 sm:text-right sm:max-w-[16rem] break-words">
                                                {{ $unavailability->description }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="py-8 text-center text-neutral-500 dark:text-neutral-400">
                                    No upcoming unavailability periods.
                                </div>
                            @endforelse
                        </div>
                    </div>
            </div>
        </div>
    </div>
</div>
