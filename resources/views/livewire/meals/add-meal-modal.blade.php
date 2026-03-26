<div>
    <flux:modal name="add-meal-modal" :show="$showModal" wire:model="showModal">
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">{{ __('Add New Meal') }}</flux:heading>
            </div>

            <form wire:submit="save" class="space-y-4">
                {{-- Meal Name --}}
                <flux:input
                    wire:model="name"
                    label="{{ __('Meal Name') }} *"
                    placeholder="{{ __('e.g., Sunday Roast') }}"
                    required
                />
                @error('name') <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror

                {{-- Date and Time --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <flux:input
                            wire:model="date"
                            type="date"
                            label="{{ __('Date') }} *"
                            required
                        />
                        @error('date') <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <flux:input
                            wire:model="time"
                            type="time"
                            label="{{ __('Time') }} *"
                            required
                        />
                        @error('time') <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Invitees --}}
                <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                        {{ __('Who to invite?') }} * <span class="text-xs font-normal text-neutral-500">({{ __('They will accept on their dashboard') }})</span>
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($users as $user)
                            <button
                                type="button"
                                wire:click="toggleInvitee({{ $user->id }})"
                                class="flex items-center gap-2 rounded-lg border-2 px-3 py-2 text-sm text-left transition-colors
                                    {{ in_array($user->id, $invitees)
                                        ? 'text-white shadow-lg transform scale-105'
                                        : 'bg-white dark:bg-zinc-800 hover:bg-neutral-50 dark:hover:bg-zinc-700/50' }}"
                                style="background-color: {{ in_array($user->id, $invitees) ? ($user->role?->color ?? '#3b82f6') : 'transparent' }}; border-color: {{ $user->role?->color ?? '#9ca3af' }}"
                            >
                                <span
                                    class="h-3 w-3 rounded-full shrink-0"
                                    style="background-color: {{ in_array($user->id, $invitees) ? '#ffffff' : ($user->role?->color ?? '#9ca3af') }}"
                                ></span>
                                <span class="{{ in_array($user->id, $invitees) ? 'text-white font-semibold' : 'text-neutral-900 dark:text-neutral-100' }}">
                                    {{ $user->name }} ({{ $user->role?->name ?? 'No Role' }})
                                </span>
                            </button>
                        @endforeach
                    </div>
                    @error('invitees') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                {{-- Notes --}}
                <div>
                    <flux:textarea
                        wire:model="notes"
                        label="{{ __('Notes (Optional)') }}"
                        placeholder="{{ __('Any special notes or instructions...') }}"
                        rows="3"
                    />
                    @error('notes') <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                {{-- Actions --}}
                <div class="flex gap-2 pt-2">
                    <x-flux.button type="submit" variant="primary" class="flex-1">
                        {{ __('Create Meal') }}
                    </x-flux.button>
                    <x-flux.button type="button" variant="ghost" wire:click="$set('showModal', false)">
                        {{ __('Cancel') }}
                    </x-flux.button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
