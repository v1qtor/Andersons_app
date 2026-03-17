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
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
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
        </div>

        {{-- Main Content 2 Columns --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start pb-6">
            
            {{-- Left Column: Today's Tasks --}}
            <div class="lg:col-span-7 bg-white dark:bg-neutral-900/50 border border-neutral-200 dark:border-neutral-700/50 rounded-2xl p-6 shadow-[0_2px_10px_-4px_rgba(0,0,0,0.05)]">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-medium text-neutral-800 dark:text-neutral-200">Today's Tasks</h2>
                    <div class="text-xl text-neutral-800 dark:text-neutral-200">{{ now()->format('n/j/Y') }}</div>
                    @php
                        $completedTasks = count($todayTasks) > 0 ? $todayTasks->where('is_complete', true)->count() : 0;
                    @endphp
                    <div class="bg-blue-50 dark:bg-blue-900/30 text-blue-500 dark:text-blue-400 text-xs px-3 py-1 rounded-md font-medium">
                        {{ $completedTasks }}/{{ $tasksCount }} Complete
                    </div>
                </div>

                <div class="flex flex-col gap-4">
                    @forelse($todayTasks as $index => $task)
                        @php
                            // Match colors from screenshot approximately
                            $colors = [
                                ['bg' => 'bg-[#f4ebfd] dark:bg-purple-900/10', 'border' => 'border-[#e8d2fa] dark:border-purple-800'],
                                ['bg' => 'bg-[#ebfbf5] dark:bg-emerald-900/10', 'border' => 'border-[#c6f3df] dark:border-emerald-800'],
                                ['bg' => 'bg-[#fef4e5] dark:bg-orange-900/10', 'border' => 'border-[#fbe1b6] dark:border-orange-800'],
                            ];
                            $color = $colors[$index % count($colors)];
                        @endphp
                        <div class="rounded-xl border {{ $color['bg'] }} {{ $color['border'] }} p-3.5 relative group">
                            <div class="flex justify-between items-start gap-4">
                                <div class="flex gap-3">
                                    <div class="pt-0.5">
                                        @if($task->is_complete)
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6 text-neutral-400">
                                                <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd" />
                                            </svg>
                                        @else
                                            <div class="size-6 rounded-full border border-neutral-400 dark:border-neutral-500 bg-white/50 dark:bg-transparent"></div>
                                        @endif
                                    </div>
                                    <div>
                                        <h4 class="text-[17px] text-neutral-800 dark:text-neutral-200">{{ $task->title }}</h4>
                                        <div class="flex items-center gap-6 mt-1.5 text-[15px] text-neutral-600 dark:text-neutral-400">
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
                                
                                <div class="flex flex-col items-end gap-1.5 pt-0.5">
                                    @php
                                        $owner = $task->users->where('pivot.is_owner', true)->first() ?? $task->users->first();
                                    @endphp
                                    @if($owner)
                                        <div class="bg-[#59636a] text-white text-[11px] px-2.5 py-0.5 rounded-full whitespace-nowrap">
                                            {{ explode(' ', $owner->name)[0] }}({{ $owner->role?->name ?? 'Role' }})
                                        </div>
                                    @endif
                                    @if($task->taskPriority)
                                        <div class="bg-cyan-50 dark:bg-cyan-900/30 text-[#0bcbb5] border border-cyan-200 dark:border-cyan-800 text-[11px] px-3 py-[1px] rounded-full lowercase">
                                            {{ $task->taskPriority->name }}
                                        </div>
                                    @elseif($index < 3)
                                        <div class="bg-cyan-50 dark:bg-cyan-900/30 text-[#0bcbb5] border border-cyan-200 dark:border-cyan-800 text-[11px] px-3 py-[1px] rounded-full lowercase">
                                            medium
                                        </div>
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
                
                {{-- Upcoming Dinner (light greenish background similar to screen) --}}
                <div class="bg-[#f0fcfc] dark:bg-neutral-900/50 border border-[#e1f7f6] dark:border-neutral-700/50 rounded-2xl p-6 shadow-[0_2px_10px_-4px_rgba(0,0,0,0.05)]">
                    <h2 class="text-2xl font-medium text-neutral-800 dark:text-neutral-200 mb-4">Upcoming Dinner</h2>
                    
                    @if($dinnerPlans)
                    <div class="bg-white dark:bg-neutral-800 border border-neutral-100 dark:border-neutral-700 rounded-xl p-4 flex items-center justify-between shadow-sm">
                        <div class="flex items-center gap-4">
                            <div class="bg-[#fdf4ee] dark:bg-orange-900/20 text-[#ea580c] size-[42px] rounded-xl flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-6">
                                  <path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2" />
                                  <path d="M7 2v20" />
                                  <path d="M21 15V2v0a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-lg font-normal text-neutral-800 dark:text-neutral-200">{{ $dinnerPlans->meal->name }}</h4>
                                <div class="text-sm text-neutral-500">
                                    {{ $dinnerPlans->date_time->format('M j, Y • H:i') }} - {{ $dinnerPlans->date_time->addHour()->format('H:i') }}
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-1.5">
                                <button class="text-red-400 hover:bg-neutral-100 border border-red-200 dark:hover:bg-neutral-700 rounded p-[3px] bg-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M22 10.5h-6m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM4 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 10.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                                    </svg>
                                </button>
                                <span class="text-blue-500 font-medium bg-blue-100 border border-blue-200 dark:bg-blue-800 dark:text-blue-200 px-2 py-0.5 rounded text-sm">0</span>
                                <button class="text-blue-500 hover:bg-neutral-100 border border-blue-200 dark:hover:bg-neutral-700 rounded p-[3px] bg-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                                    </svg>
                                </button>
                            </div>
                            <button class="bg-[#1bcc8a] hover:bg-[#15ab73] text-white text-[15px] px-5 py-1.5 rounded-lg transition-colors">
                                Join
                            </button>
                        </div>
                    </div>
                    @else
                        <div class="bg-white dark:bg-neutral-800 border border-neutral-100 dark:border-neutral-700 rounded-xl p-4 shadow-sm text-center text-neutral-500">
                            No dinner planned for today.
                        </div>
                    @endif
                </div>

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
                </div>

            </div>
        </div>
    </div>
</div>
