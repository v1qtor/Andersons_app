<div x-data="{ deletingId: null }">
    {{-- Dietary Information Banner (management roles only) --}}
    @if($dietaryUsers->isNotEmpty() && $canManage)
        <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-900/20">
            <div class="flex items-center gap-2 mb-2">
                <svg class="h-5 w-5 text-amber-600 dark:text-amber-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd" />
                </svg>
                <span class="font-semibold text-amber-800 dark:text-amber-300">{{ __('Dietary Information:') }}</span>
            </div>
            <ul class="space-y-2 ml-7">
                @foreach($dietaryUsers as $user)
                    <li wire:key="dietary-{{ $user->id }}" class="flex items-start gap-2 text-sm text-amber-700 dark:text-amber-400">
                        <span class="mt-2 h-1.5 w-1.5 rounded-full bg-amber-500 dark:bg-amber-400"></span>
                        <span>
                            <span class="font-semibold" style="color: {{ $user->role?->color ?? '#92400e' }}">
                                {{ $user->name }} ({{ $user->role?->name ?? 'No Role' }})
                            </span>
                        @if($user->allergies->isNotEmpty())
                            has <span class="font-semibold">allergies:</span> {{ $user->allergies->pluck('name')->implode(', ') }}
                        @endif
                        @if($user->allergies->isNotEmpty() && $user->preferences->isNotEmpty())
                            &middot;
                        @endif
                        @if($user->preferences->isNotEmpty())
                            has <span class="font-semibold">preferences:</span> {{ $user->preferences->pluck('name')->implode(', ') }}
                        @endif
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Meal Cards --}}
    <div class="space-y-4">
        @forelse($meals as $meal)
            <div wire:key="meal-{{ $meal->id }}" class="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-700 dark:bg-zinc-800">

                {{-- Header Row: Name + Prepared Status + Delete --}}
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">
                            {{ $meal->meal?->name ?? __('Unnamed Meal') }}
                        </h3>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">
                            {{ $meal->date_time->translatedFormat('l, d F Y') }} {{ __('at') }} {{ $meal->date_time->format('H:i') }}
                        </p>
                    </div>

                    <div class="flex items-center gap-3 shrink-0">
                        {{-- Prepared Status --}}
                        @if($canTogglePrepared)
                            {{-- Interactive toggle for whoever can change prepared state --}}
                            @if($meal->is_prepared)
                                <button
                                    wire:click="togglePrepared({{ $meal->id }})"
                                    class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700 hover:bg-emerald-200 dark:bg-emerald-900/40 dark:text-emerald-400 dark:hover:bg-emerald-900/60 transition-colors"
                                >
                                    <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                                    </svg>
                                    {{ __('Prepared') }}
                                    <span class="ml-0.5 font-normal opacity-60">· {{ __('Unmark') }}</span>
                                </button>
                            @else
                                <button
                                    wire:click="togglePrepared({{ $meal->id }})"
                                    class="inline-flex items-center gap-1 rounded-full border border-emerald-300 px-3 py-1 text-xs font-medium text-emerald-700 hover:bg-emerald-50 dark:border-emerald-700 dark:text-emerald-400 dark:hover:bg-emerald-900/30 transition-colors"
                                    title="{{ __('Mark as prepared') }}"
                                >
                                    <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                                    </svg>
                                    {{ __('Mark Prepared') }}
                                </button>
                            @endif
                        @elseif($meal->is_prepared)
                            {{-- Read-only prepared badge for everyone else --}}
                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400">
                                <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                                </svg>
                                {{ __('Prepared') }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 rounded-full bg-neutral-100 px-3 py-1 text-xs font-medium text-neutral-500 dark:bg-zinc-700 dark:text-neutral-400">
                                <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z" clip-rule="evenodd" />
                                </svg>
                                {{ __('Not yet prepared') }}
                            </span>
                        @endif

                        {{-- Edit Invitees Button (management roles only) --}}
                        @if($canManage)
                            <button
                                type="button"
                                wire:click="$dispatch('editInvitees', { mealId: {{ $meal->id }} })"
                                class="text-neutral-500 hover:text-indigo-600 dark:text-neutral-400 dark:hover:text-indigo-400 transition-colors"
                                title="{{ __('Edit invitees') }}"
                            >
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10 9a3 3 0 100-6 3 3 0 000 6zM6 8a2 2 0 11-4 0 2 2 0 014 0zM1.49 15.326a.78.78 0 01-.358-.442 3 3 0 014.308-3.516 6.484 6.484 0 00-1.905 3.959c-.023.222-.014.442.025.654a4.97 4.97 0 01-2.07-.655zM16.44 15.98a4.97 4.97 0 002.07-.654.78.78 0 00.357-.442 3 3 0 00-4.308-3.517 6.484 6.484 0 011.907 3.96 2.32 2.32 0 01-.026.654zM18 8a2 2 0 11-4 0 2 2 0 014 0zM5.304 16.19a.844.844 0 01-.277-.71 5 5 0 019.947 0 .843.843 0 01-.277.71A6.975 6.975 0 0110 18a6.974 6.974 0 01-4.696-1.81z" />
                                </svg>
                            </button>
                        @endif

                        {{-- Delete Button (management roles only) --}}
                        @if($canManage)
                            <button
                                type="button"
                                @click="deletingId = {{ $meal->id }}; $dispatch('modal-show', { name: 'confirm-delete-meal' })"
                                class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 transition-colors"
                                title="{{ __('Delete meal') }}"
                            >
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Invited / Accepted attendees + Guests --}}
                @if($meal->subscribers->isNotEmpty())
                    <div class="mt-4 space-y-3">
                        @if($meal->invitedSubscribers->isNotEmpty())
                            <div>
                                <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400 mb-2">
                                    <svg class="inline h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M7 8a3 3 0 100-6 3 3 0 000 6zM14.5 9a2.5 2.5 0 100-5 2.5 2.5 0 000 5zM1.615 16.428a1.224 1.224 0 01-.569-1.175 6.002 6.002 0 0111.908 0c.058.467-.172.92-.57 1.174A9.953 9.953 0 017 18a9.953 9.953 0 01-5.385-1.572zM14.5 16h-.106c.07-.297.088-.611.048-.933a7.47 7.47 0 00-1.588-3.755 4.502 4.502 0 015.874 2.636.818.818 0 01-.36.98A7.465 7.465 0 0114.5 16z" />
                                    </svg>
                                    {{ __('Invited') }} ({{ $meal->invitedSubscribers->count() }}):
                                </p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($meal->invitedSubscribers as $subscriber)
                                        <span
                                            wire:key="invited-{{ $meal->id }}-{{ $subscriber->id }}"
                                            class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium text-white"
                                            style="background-color: {{ $subscriber->role?->color ?? '#6b7280' }}"
                                        >
                                            {{ $subscriber->name }} ({{ $subscriber->role?->name ?? 'No Role' }})
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($meal->acceptedSubscribers->isNotEmpty())
                            <div>
                                <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400 mb-2">
                                    <svg class="inline h-4 w-4 mr-1 text-emerald-600 dark:text-emerald-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                                    </svg>
                                    {{ __('Accepted') }} ({{ $meal->acceptedSubscribers->count() }}):
                                </p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($meal->acceptedSubscribers as $subscriber)
                                        <span
                                            wire:key="accepted-{{ $meal->id }}-{{ $subscriber->id }}"
                                            class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium text-white"
                                            style="background-color: {{ $subscriber->role?->color ?? '#6b7280' }}"
                                        >
                                            {{ $subscriber->name }} ({{ $subscriber->role?->name ?? 'No Role' }})
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($meal->guests->isNotEmpty())
                            <div>
                                <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400 mb-2">
                                    <svg class="inline h-4 w-4 mr-1 text-sky-600 dark:text-sky-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M10 9a3 3 0 100-6 3 3 0 000 6zM6 8a2 2 0 11-4 0 2 2 0 014 0zM1.49 15.326a.78.78 0 01-.358-.442 3 3 0 014.308-3.516 6.484 6.484 0 00-1.905 3.959c-.023.222-.014.442.025.654a4.97 4.97 0 01-2.07-.655zM16.44 15.98a4.97 4.97 0 002.07-.654.78.78 0 00.357-.442 3 3 0 00-4.308-3.517 6.484 6.484 0 011.907 3.96 2.32 2.32 0 01-.026.654zM18 8a2 2 0 11-4 0 2 2 0 014 0zM5.304 16.19a.844.844 0 01-.277-.71 5 5 0 019.947 0 .843.843 0 01-.277.71A6.975 6.975 0 0110 18a6.974 6.974 0 01-4.696-1.81z" />
                                    </svg>
                                    {{ __('Guests') }} ({{ $meal->guests->count() }}):
                                </p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($meal->guests as $guest)
                                        <span
                                            wire:key="guest-{{ $meal->id }}-{{ $guest->id }}"
                                            class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium text-white"
                                            style="background-color: {{ $guest->invitedBy?->role?->color ?? '#6b7280' }}"
                                        >
                                            {{ $guest->name }} ({{ __('invited by') }} {{ $guest->invitedBy?->name ?? __('unknown') }})
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Attendee Dietary Info (management roles only) --}}
                @if($canManage && ($meal->subscriberAllergies->isNotEmpty() || $meal->subscriberPreferences->isNotEmpty()))
                    <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 p-3 dark:border-amber-800 dark:bg-amber-900/20">
                        <div class="flex items-center gap-2 mb-1">
                            <svg class="h-4 w-4 text-amber-600 dark:text-amber-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd" />
                            </svg>
                            <span class="text-sm font-semibold text-amber-800 dark:text-amber-300">{{ __('Attendee Dietary Information:') }}</span>
                        </div>
                        @if($meal->subscriberAllergies->isNotEmpty())
                            <p class="text-sm text-amber-700 dark:text-amber-400 ml-6">
                                <span class="font-semibold">⚠ {{ __('Allergies:') }}</span> {{ $meal->subscriberAllergies->implode(', ') }}
                            </p>
                        @endif
                        @if($meal->subscriberPreferences->isNotEmpty())
                            <p class="text-sm text-amber-700 dark:text-amber-400 ml-6">
                                <span class="font-semibold">{{ __('Preferences:') }}</span> {{ $meal->subscriberPreferences->implode(', ') }}
                            </p>
                        @endif
                    </div>
                @endif

                {{-- Guest Notes (management roles only) --}}
                @if($canManage && $meal->guestsWithNotes->isNotEmpty())
                    <div class="mt-3 rounded-lg border border-sky-200 bg-sky-50 p-3 dark:border-sky-800 dark:bg-sky-900/20">
                        <div class="flex items-center gap-2 mb-1">
                            <svg class="h-4 w-4 text-sky-600 dark:text-sky-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zM7 8H5v2h2V8zm2 0h2v2H9V8zm6 0h-2v2h2V8z" clip-rule="evenodd" />
                            </svg>
                            <span class="text-sm font-semibold text-sky-800 dark:text-sky-300">{{ __('Guest Notes:') }}</span>
                        </div>
                        <ul class="space-y-1 ml-6">
                            @foreach($meal->guestsWithNotes as $guest)
                                <li wire:key="guest-note-{{ $meal->id }}-{{ $guest->id }}" class="text-sm text-sky-700 dark:text-sky-400">
                                    <span class="font-semibold">{{ $guest->name }}</span>
                                    <span class="opacity-70">({{ __('invited by') }} {{ $guest->invitedBy?->name ?? __('unknown') }})</span>:
                                    {{ $guest->note }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Notes --}}
                @if($meal->notes)
                    <div class="mt-3 rounded-lg border border-neutral-200 bg-neutral-50 p-3 dark:border-neutral-700 dark:bg-zinc-900/50">
                        <p class="text-sm text-neutral-600 dark:text-neutral-400">
                            <span class="font-semibold">{{ __('Notes:') }}</span> {{ $meal->notes }}
                        </p>
                    </div>
                @endif

                {{-- My Participation (only for roles that can participate) --}}
                @if($canParticipate)
                    <div class="mt-4 flex items-center justify-between border-t border-neutral-100 pt-4 dark:border-neutral-700">
                        <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">{{ __('My participation') }}</span>
                        @if($meal->mySubscription)
                            @if($meal->mySubscription->pivot->confirmed)
                                <button
                                    wire:click="toggleParticipation({{ $meal->id }})"
                                    class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-4 py-1.5 text-sm font-semibold text-emerald-700 hover:bg-emerald-200 dark:bg-emerald-900/40 dark:text-emerald-400 dark:hover:bg-emerald-900/60 transition-colors"
                                >
                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                                    </svg>
                                    {{ __('Confirmed') }}
                                    <span class="ml-0.5 font-normal opacity-60">· {{ __('Unconfirm') }}</span>
                                </button>
                            @else
                                <button
                                    wire:click="toggleParticipation({{ $meal->id }})"
                                    class="inline-flex items-center gap-1.5 rounded-full border border-neutral-300 px-4 py-1.5 text-sm font-medium text-neutral-600 hover:border-emerald-400 hover:text-emerald-600 dark:border-neutral-600 dark:text-neutral-400 dark:hover:border-emerald-600 dark:hover:text-emerald-400 transition-colors"
                                >
                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                                    </svg>
                                    {{ __('Confirm participation') }}
                                </button>
                            @endif
                        @else
                            <span class="text-sm text-neutral-400 dark:text-neutral-500 italic">
                                {{ $canManage ? __('You have not added yourself to this meal') : __('Not invited') }}
                            </span>
                        @endif
                    </div>
                @endif

            </div>
        @empty
            <div class="rounded-xl border border-neutral-200 bg-white p-12 text-center dark:border-neutral-700 dark:bg-zinc-800">
                <p class="text-neutral-500 dark:text-neutral-400">{{ __('No meals planned yet.') }}</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($meals->hasPages())
        <div class="mt-6">
            {{ $meals->onEachSide(1)->links('livewire::simple-tailwind') }}
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    <flux:modal name="confirm-delete-meal">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Delete Meal') }}</flux:heading>
                <flux:subheading>{{ __('Are you sure you want to delete this planned meal? This action cannot be undone.') }}</flux:subheading>
            </div>

            <div class="flex gap-2 justify-end">
                <x-flux.button variant="ghost" @click="$dispatch('modal-close', { name: 'confirm-delete-meal' })">{{ __('Cancel') }}</x-flux.button>
                <x-flux.button variant="danger" @click="$wire.deleteMeal(deletingId); $dispatch('modal-close', { name: 'confirm-delete-meal' })">{{ __('Delete') }}</x-flux.button>
            </div>
        </div>
    </flux:modal>
</div>
