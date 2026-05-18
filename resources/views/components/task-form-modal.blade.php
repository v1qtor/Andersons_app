@props([
    'editingTaskId' => null,
    'taskCategories' => collect(),
    'taskPriorities' => collect(),
    'allUsers' => collect(),
    'isAdmin' => false,
    'taskOwnerId' => null,
    'assignedUserIds' => [],
    'locations' => collect(),
    'selectedLocationIds' => [],
    'collaborationUserIds' => [],
    'pendingOutgoingUserIds' => [],
    'unavailableUserIds' => [],
])

<x-ui.detail-modal
    :show="true"
    :title="$editingTaskId ? __('Edit Task') : __('New Task')"
    zIndex="z-[60]"
    maxWidth="max-w-lg"
    closeAction="$set('showTaskModal', false)"
    escapeAction="$wire.set('showTaskModal', false)"
    :lockBodyScroll="true"
    panelClass="rounded-2xl shadow-2xl max-h-[90vh]"
    bodyPadding="p-0"
    titleClass="text-xl font-bold text-neutral-900 dark:text-neutral-100"
    headerClass="z-10"
>

        <form wire:submit="saveTask" class="p-6 space-y-4">
            <div>
                <flux:input wire:model="title" :label="__('Title')" placeholder="{{ __('Task title') }}" required />
                @error('title') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div>
                <flux:textarea wire:model="description" :label="__('Description')" placeholder="{{ __('Optional description') }}" rows="3" />
                @error('description') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <flux:input wire:model="startDate" :label="__('Start Date & Time')" type="datetime-local" required />
                    @error('startDate') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <flux:input wire:model="endDate" :label="__('End Date & Time')" type="datetime-local" />
                    @error('endDate') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <flux:select wire:model.live="taskCategoryId" :label="__('Category')" placeholder="{{ __('Select category') }}">
                        @foreach ($taskCategories as $cat)
                            <flux:select.option value="{{ $cat->id }}">{{ $cat->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    @error('taskCategoryId') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <flux:select wire:model.live="taskPriorityId" :label="__('Priority')" placeholder="{{ __('Select priority') }}">
                        @foreach ($taskPriorities as $pri)
                            <flux:select.option value="{{ $pri->id }}">{{ $pri->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    @error('taskPriorityId') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- ─── Locations ─── --}}
            @if ($locations->isNotEmpty())
                <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                        {{ __('Locations') }}
                    </label>
                    <div class="flex flex-wrap gap-2 p-3 rounded-lg border border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-zinc-900/50 max-h-32 overflow-y-auto">
                        @foreach ($locations as $location)
                            @php $isSelected = in_array($location->id, $selectedLocationIds); @endphp
                            <button
                                type="button"
                                wire:click="toggleLocation({{ $location->id }})"
                                class="px-3 py-1.5 rounded-full text-xs font-medium transition-all border-2
                                    {{ $isSelected
                                        ? 'border-teal-500 bg-teal-500 text-white dark:border-teal-400 dark:bg-teal-400 dark:text-zinc-900'
                                        : 'border-neutral-300 bg-transparent text-neutral-600 hover:border-teal-400 dark:border-neutral-600 dark:text-neutral-400 dark:hover:border-teal-500' }}"
                            >
                                📍 {{ $location->name }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- ─── Task Owner (admin only) ─── --}}
            @if ($isAdmin)
                <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                        {{ __('Task Owner') }}
                    </label>
                    <div class="flex flex-wrap gap-2 p-3 rounded-lg border border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-zinc-900/50 max-h-40 overflow-y-auto">
                        @foreach ($allUsers as $user)
                            @if(in_array($user->id, $unavailableUserIds))
                                @continue
                            @endif
                            @php
                                $isOwner = $user->id === $taskOwnerId;
                                $roleColor = $user->role?->color ?? '#6366f1';
                            @endphp
                            <button
                                type="button"
                                wire:click="setTaskOwner({{ $user->id }})"
                                class="px-3 py-1.5 rounded-full text-xs font-medium transition-all border-2"
                                style="
                                    border-color: {{ $roleColor }};
                                    background-color: {{ $isOwner ? $roleColor : 'transparent' }};
                                    color: {{ $isOwner ? '#fff' : $roleColor }};
                                "
                            >
                                {{ $user->name }}
                                @if ($isOwner)
                                    <span class="ml-0.5">👑</span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                        {{ __('The owner can edit and delete this task. Only one owner allowed.') }}
                        @if(!empty($unavailableUserIds))
                            <span class="text-amber-500 dark:text-amber-400"> · {{ __('Users unavailable during this period are hidden.') }}</span>
                        @endif
                    </p>
                </div>
            @endif

            {{-- ─── Assigned Users (admin direct assign) ─── --}}
            @if ($isAdmin)
                <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                        {{ __('Assigned Users') }}
                    </label>
                    <div class="flex flex-wrap gap-2 p-3 rounded-lg border border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-zinc-900/50 max-h-40 overflow-y-auto">
                        @foreach ($allUsers as $user)
                            @if(in_array($user->id, $unavailableUserIds))
                                @continue
                            @endif
                            @php
                                $isAssigned = in_array($user->id, $assignedUserIds);
                                $roleColor = $user->role?->color ?? '#6366f1';
                            @endphp
                            <button
                                type="button"
                                wire:click="toggleAssignedUser({{ $user->id }})"
                                class="px-3 py-1.5 rounded-full text-xs font-medium transition-all border-2"
                                style="
                                    border-color: {{ $roleColor }};
                                    background-color: {{ $isAssigned ? $roleColor : 'transparent' }};
                                    color: {{ $isAssigned ? '#fff' : $roleColor }};
                                "
                            >
                                {{ $user->name }}
                            </button>
                        @endforeach
                    </div>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                        {{ __('Select multiple users to assign to this task.') }}
                        @if(!empty($unavailableUserIds))
                            <span class="text-amber-500 dark:text-amber-400"> · {{ __('Users unavailable during this period are hidden.') }}</span>
                        @endif
                    </p>
                </div>
            @endif

            {{-- ─── Request Collaboration (non-admin) ─── --}}
            @if (! $isAdmin)
                <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                        {{ __('Request Collaboration') }}
                    </label>
                    <div class="flex flex-wrap gap-2 p-3 rounded-lg border border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-zinc-900/50 max-h-40 overflow-y-auto">
                        @foreach ($allUsers as $user)
                            @if ($user->id === auth()->id())
                                @continue
                            @endif
                            @if (in_array($user->id, $unavailableUserIds))
                                @continue
                            @endif
                            @php
                                $isPendingAlready = in_array($user->id, $pendingOutgoingUserIds);
                                $isSelected = in_array($user->id, $collaborationUserIds);
                                $roleColor = $user->role?->color ?? '#6366f1';
                            @endphp

                            @if ($isPendingAlready)
                                {{-- Already pending — show as disabled --}}
                                <span
                                    class="px-3 py-1.5 rounded-full text-xs font-medium border-2 opacity-50 cursor-not-allowed"
                                    style="border-color: {{ $roleColor }}; color: {{ $roleColor }};"
                                    title="{{ __('Request already pending') }}"
                                >
                                    {{ $user->name }} ⏳
                                </span>
                            @else
                                <button
                                    type="button"
                                    wire:click="toggleCollaborationUser({{ $user->id }})"
                                    class="px-3 py-1.5 rounded-full text-xs font-medium transition-all border-2"
                                    style="
                                        border-color: {{ $roleColor }};
                                        background-color: {{ $isSelected ? $roleColor : 'transparent' }};
                                        color: {{ $isSelected ? '#fff' : $roleColor }};
                                    "
                                >
                                    {{ $user->name }}
                                </button>
                            @endif
                        @endforeach
                    </div>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                        {{ __('Selected users will receive a collaboration request.') }}
                        @if(!empty($unavailableUserIds))
                            <span class="text-amber-500 dark:text-amber-400"> · {{ __('Users unavailable during this period are hidden.') }}</span>
                        @endif
                    </p>
                </div>
            @endif

            @if ($editingTaskId)
                <div class="flex items-center gap-3">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model="isComplete" class="sr-only peer">
                        <div class="w-9 h-5 bg-neutral-200 peer-focus:outline-none rounded-full peer dark:bg-neutral-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-neutral-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-neutral-600 peer-checked:bg-green-500"></div>
                    </label>
                    <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">{{ __('Completed') }}</span>
                </div>
            @endif

            <div class="flex justify-end gap-3 pt-4 border-t border-neutral-200 dark:border-neutral-700">
                <x-flux.button variant="ghost" wire:click="$set('showTaskModal', false)">
                    {{ __('Cancel') }}
                </x-flux.button>
                <x-flux.button type="submit" variant="primary">
                    {{ $editingTaskId ? __('Update Task') : __('Create Task') }}
                </x-flux.button>
            </div>
        </form>
    </x-ui.detail-modal>

