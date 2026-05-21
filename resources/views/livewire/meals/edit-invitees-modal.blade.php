<div>
    <flux:modal name="edit-invitees-modal" :show="$showModal" wire:model="showModal">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Edit Invitees') }}</flux:heading>
                @if($mealId)
                    <flux:subheading>
                        {{ $mealName }} &middot; {{ $mealDateTime }}
                    </flux:subheading>
                @endif
            </div>

            <form wire:submit="save" class="space-y-4" x-data="{ invitees: @entangle('invitees') }">
                <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                        {{ __('Who to invite?') }} <span class="text-xs font-normal text-neutral-500">({{ __('Changes are applied when you confirm') }})</span>
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($users as $user)
                            <button
                                wire:key="edit-invitee-{{ $mealId }}-{{ $user->id }}"
                                type="button"
                                @click="invitees = invitees.includes({{ $user->id }}) ? invitees.filter(id => id !== {{ $user->id }}) : [...invitees, {{ $user->id }}]"
                                class="flex items-center gap-2 rounded-lg border-2 px-3 py-2 text-sm text-left transition-colors"
                                :class="invitees.includes({{ $user->id }})
                                    ? 'text-white'
                                    : 'bg-white dark:bg-zinc-800 hover:bg-neutral-50 dark:hover:bg-zinc-700/50'"
                                :style="`background-color: ${invitees.includes({{ $user->id }}) ? '{{ $user->role?->color ?? '#3b82f6' }}' : 'transparent'}; border-color: {{ $user->role?->color ?? '#9ca3af' }};`"
                            >
                                <span
                                    class="h-3 w-3 rounded-full shrink-0"
                                    :style="`background-color: ${invitees.includes({{ $user->id }}) ? '#ffffff' : '{{ $user->role?->color ?? '#9ca3af' }}'};`"
                                ></span>
                                <span :class="invitees.includes({{ $user->id }}) ? 'text-white font-semibold' : 'text-neutral-900 dark:text-neutral-100'">
                                    {{ $user->name }} ({{ $user->role?->name ?? 'No Role' }})
                                </span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="flex gap-2 pt-2">
                    <x-flux.button type="submit" variant="primary" class="flex-1">
                        {{ __('Confirm Selection') }}
                    </x-flux.button>
                    <x-flux.button type="button" variant="ghost" wire:click="$set('showModal', false)">
                        {{ __('Cancel') }}
                    </x-flux.button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
