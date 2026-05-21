<div>
    {{-- Toast --}}
    <div
        x-data="{
            toasts: [],
            add(message, type) {
                const id = Date.now();
                this.toasts.push({ id, message, type, show: true });
                setTimeout(() => {
                    const toast = this.toasts.find(t => t.id === id);
                    if (toast) toast.show = false;
                    setTimeout(() => { this.toasts = this.toasts.filter(t => t.id !== id); }, 600);
                }, 5000);
            }
        }"
        @toast.window="add($event.detail.message, $event.detail.type)"
        class="fixed top-4 right-4 z-50 flex flex-col gap-2 pointer-events-none"
    >
        <template x-for="toast in toasts" :key="toast.id">
            <div
                x-show="toast.show"
                x-transition:leave="transition ease-in duration-500"
                x-transition:leave-start="opacity-100 translate-x-0"
                x-transition:leave-end="opacity-0 translate-x-24"
                :class="toast.type === 'error'
                    ? 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800'
                    : 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800'"
                class="rounded-lg shadow-lg p-4 max-w-md pointer-events-auto"
            >
                <div class="flex items-center gap-3">
                    <template x-if="toast.type !== 'error'">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </template>
                    <template x-if="toast.type === 'error'">
                        <svg class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-9a1 1 0 012 0v4a1 1 0 01-2 0V9zm1-4a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd" />
                        </svg>
                    </template>
                    <p
                        :class="toast.type === 'error' ? 'text-red-800 dark:text-red-300' : 'text-green-800 dark:text-green-300'"
                        class="font-medium text-sm"
                        x-text="toast.message"
                    ></p>
                </div>
            </div>
        </template>
    </div>

    <div class="w-full max-w-4xl mx-auto flex flex-col gap-6">

        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-medium tracking-tight text-neutral-900 dark:text-white">Unavailabilities</h1>
                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                    @if($isAdmin)
                        View and manage unavailability periods for all staff.
                    @else
                        Manage your unavailable periods. You cannot add a period that conflicts with an existing task.
                    @endif
                </p>
            </div>
            @if(! $showForm)
                <flux:button variant="primary" wire:click="openForm" icon="plus" class="sm:self-start">
                    Add Unavailability
                </flux:button>
            @endif
        </div>

        {{-- ─── Admin Filter Bar ─── --}}
        @if($isAdmin && $filterUsers->isNotEmpty())
            <div class="bg-white dark:bg-neutral-900/50 border border-neutral-200 dark:border-neutral-700/50 rounded-2xl p-4 shadow-sm">
                <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-3">Filter by person</p>
                <div class="flex flex-wrap gap-2">

                    {{-- All --}}
                    <button
                        wire:click="setFilter(null)"
                        class="px-3 py-1.5 rounded-full text-xs font-semibold transition-all border-2
                            {{ (! $filterUserId && ! $myOnly)
                                ? 'border-neutral-800 bg-neutral-800 text-white dark:border-neutral-200 dark:bg-neutral-200 dark:text-neutral-900'
                                : 'border-neutral-300 text-neutral-600 hover:border-neutral-500 dark:border-neutral-600 dark:text-neutral-400 dark:hover:border-neutral-400' }}"
                    >
                        Everyone
                    </button>

                    {{-- My Unavailabilities --}}
                    <button
                        wire:click="setMyOnly"
                        class="px-3 py-1.5 rounded-full text-xs font-semibold transition-all border-2
                            {{ $myOnly
                                ? 'border-blue-600 bg-blue-600 text-white dark:border-blue-400 dark:bg-blue-400 dark:text-blue-900'
                                : 'border-blue-300 text-blue-600 hover:border-blue-500 dark:border-blue-700 dark:text-blue-400 dark:hover:border-blue-500' }}"
                    >
                        ⭐ My Unavailabilities
                    </button>

                    {{-- Separator --}}
                    <div class="w-px bg-neutral-200 dark:bg-neutral-700 self-stretch mx-1"></div>

                    {{-- Per user (excluding the logged-in admin themselves) --}}
                    @foreach($filterUsers as $user)
                        @if($user->id === auth()->id()) @continue @endif
                        @php $roleColor = $user->role?->color ?? '#6366f1'; @endphp
                        <button
                            wire:click="setFilter({{ $user->id }})"
                            class="px-3 py-1.5 rounded-full text-xs font-semibold transition-all border-2"
                            style="
                                border-color: {{ $roleColor }};
                                background-color: {{ $filterUserId === $user->id ? $roleColor : 'transparent' }};
                                color: {{ $filterUserId === $user->id ? '#fff' : $roleColor }};
                            "
                        >
                            {{ $user->name }}
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Add / Edit Form --}}
        @if($showForm)
            <div class="bg-white dark:bg-neutral-900/50 border border-neutral-200 dark:border-neutral-700/50 rounded-2xl p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200 mb-5">
                    {{ $editingId ? 'Edit Unavailability Period' : 'New Unavailability Period' }}
                </h2>

                <form wire:submit="save" class="space-y-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Start Date <span class="text-red-500">*</span></label>
                            <input
                                type="date"
                                wire:model="startDate"
                                @if(! $isAdmin) min="{{ now()->format('Y-m-d') }}" @endif
                                class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm"
                            >
                            @error('startDate')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Start Time <span class="text-red-500">*</span></label>
                            <input
                                type="time"
                                wire:model="startTime"
                                class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm"
                            >
                            @error('startTime')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">End Date <span class="text-red-500">*</span></label>
                            <input
                                type="date"
                                wire:model="endDate"
                                @if(! $isAdmin) min="{{ now()->format('Y-m-d') }}" @endif
                                class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm"
                            >
                            @error('endDate')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">End Time <span class="text-red-500">*</span></label>
                            <input
                                type="time"
                                wire:model="endTime"
                                class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm"
                            >
                            @error('endTime')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Description <span class="text-neutral-400 font-normal">(optional)</span></label>
                        <textarea
                            wire:model="description"
                            rows="3"
                            placeholder="e.g. Doctor's appointment, personal leave..."
                            class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm resize-none"
                        ></textarea>
                        @error('description')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-3 pt-1">
                        <flux:button type="submit" variant="primary">
                            {{ $editingId ? 'Update' : 'Save' }}
                        </flux:button>
                        <flux:button type="button" variant="ghost" wire:click="closeForm">Cancel</flux:button>
                    </div>
                </form>
            </div>
        @endif

        {{-- Upcoming Unavailabilities --}}
        <div class="bg-white dark:bg-neutral-900/50 border border-neutral-200 dark:border-neutral-700/50 rounded-2xl p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200 mb-4">Upcoming</h2>

            @forelse($upcoming as $period)
                @php
                    $isFuture   = $period->start_date->isFuture();
                    $canEdit    = $isAdmin || ($period->user_id === auth()->id() && $isFuture);
                    $canDelete  = $isAdmin || ($period->user_id === auth()->id() && $isFuture);
                    $roleColor  = $period->user->role?->color ?? '#6366f1';
                @endphp
                <div class="flex items-start justify-between gap-4 py-4 {{ !$loop->last ? 'border-b border-neutral-100 dark:border-neutral-700/50' : '' }}">
                    <div class="flex items-start gap-4">
                        {{-- Icon --}}
                        <div class="bg-red-50 dark:bg-red-900/20 text-red-500 dark:text-red-400 size-10 rounded-xl flex items-center justify-center shrink-0 mt-0.5">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="size-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                            </svg>
                        </div>

                        {{-- Details --}}
                        <div>
                            {{-- Admin: show user name badge --}}
                            @if($isAdmin)
                                <span
                                    class="inline-block text-xs font-semibold px-2 py-0.5 rounded-full mb-1"
                                    style="background-color: {{ $roleColor }}22; color: {{ $roleColor }}; border: 1px solid {{ $roleColor }}44;"
                                >
                                    {{ $period->user->name }}
                                </span>
                            @endif

                            <p class="text-sm font-semibold text-neutral-800 dark:text-neutral-200">
                                {{ $period->start_date->format('D, d M Y') }}
                                @if($period->start_date->format('Y-m-d') !== $period->end_date->format('Y-m-d'))
                                    → {{ $period->end_date->format('D, d M Y') }}
                                @endif
                            </p>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">
                                {{ $period->start_date->format('H:i') }} – {{ $period->end_date->format('H:i') }}
                            </p>
                            @if($period->description)
                                <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1 italic">{{ $period->description }}</p>
                            @endif
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-1 shrink-0">
                        @if($canEdit)
                            <button
                                wire:click="openEdit({{ $period->id }})"
                                class="text-neutral-400 hover:text-blue-600 dark:text-neutral-500 dark:hover:text-blue-400 transition-colors p-1.5 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20"
                                title="Edit"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="size-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                </svg>
                            </button>
                        @endif

                        @if($canDelete)
                            <button
                                wire:click="confirmDelete({{ $period->id }})"
                                class="text-neutral-400 hover:text-red-600 dark:text-neutral-500 dark:hover:text-red-400 transition-colors p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20"
                                title="Delete"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="size-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
                            </button>
                        @elseif(! $isFuture && ! $isAdmin)
                            <span class="text-xs text-neutral-400 dark:text-neutral-500 italic mt-1 px-1">In progress</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="py-10 text-center text-neutral-400 dark:text-neutral-500 text-sm">
                    No upcoming unavailability periods.
                </div>
            @endforelse
        </div>

        {{-- Past Unavailabilities --}}
        @if($past->isNotEmpty())
            <div class="bg-white dark:bg-neutral-900/50 border border-neutral-200 dark:border-neutral-700/50 rounded-2xl p-6 shadow-sm opacity-70">
                <h2 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200 mb-4">Past</h2>

                @foreach($past as $period)
                    @php $roleColor = $period->user->role?->color ?? '#6366f1'; @endphp
                    <div class="flex items-start gap-4 py-4 {{ !$loop->last ? 'border-b border-neutral-100 dark:border-neutral-700/50' : '' }}">
                        <div class="bg-neutral-100 dark:bg-neutral-800 text-neutral-400 size-10 rounded-xl flex items-center justify-center shrink-0 mt-0.5">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="size-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                            </svg>
                        </div>
                        <div>
                            @if($isAdmin)
                                <span
                                    class="inline-block text-xs font-semibold px-2 py-0.5 rounded-full mb-1"
                                    style="background-color: {{ $roleColor }}22; color: {{ $roleColor }}; border: 1px solid {{ $roleColor }}44;"
                                >
                                    {{ $period->user->name }}
                                </span>
                            @endif
                            <p class="text-sm font-semibold text-neutral-500 dark:text-neutral-400">
                                {{ $period->start_date->format('D, d M Y') }}
                                @if($period->start_date->format('Y-m-d') !== $period->end_date->format('Y-m-d'))
                                    → {{ $period->end_date->format('D, d M Y') }}
                                @endif
                            </p>
                            <p class="text-sm text-neutral-400 dark:text-neutral-500 mt-0.5">
                                {{ $period->start_date->format('H:i') }} – {{ $period->end_date->format('H:i') }}
                            </p>
                            @if($period->description)
                                <p class="text-sm text-neutral-400 dark:text-neutral-500 mt-1 italic">{{ $period->description }}</p>
                            @endif
                        </div>
                        {{-- Admin can delete past records too --}}
                        @if($isAdmin)
                            <div class="ml-auto">
                                <button
                                    wire:click="confirmDelete({{ $period->id }})"
                                    class="text-neutral-300 hover:text-red-500 dark:text-neutral-600 dark:hover:text-red-400 transition-colors p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20"
                                    title="Delete"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="size-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

    </div>

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteModal)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
            wire:click.self="cancelDelete"
            @keydown.escape.window="$wire.cancelDelete()"
        >
            <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-2xl max-w-sm w-full p-6">
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/30 mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="size-6 text-red-600 dark:text-red-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-neutral-100 mb-2">Delete Unavailability</h3>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-6">Are you sure you want to delete this unavailability period? This action cannot be undone.</p>
                    <div class="flex justify-center gap-3">
                        <flux:button variant="ghost" wire:click="cancelDelete">Cancel</flux:button>
                        <flux:button variant="danger" wire:click="delete">Delete</flux:button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
