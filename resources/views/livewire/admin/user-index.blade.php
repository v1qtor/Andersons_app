<section class="w-full">
    <div class="max-w-5xl mx-auto">
        {{-- Header --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <flux:heading size="xl">{{ __('Users Management') }}</flux:heading>
                <flux:subheading>{{ __('Manage all users in the system') }}</flux:subheading>
            </div>
            <x-flux.button variant="primary" :href="route('admin.users.create')" wire:navigate icon="plus">
                {{ __('Create User') }}
            </x-flux.button>
        </div>

        {{-- Flash Message --}}
        <x-ui.flash-alert duration="5000" />

        {{-- Search --}}
        <div class="mb-6">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="{{ __('Search users by name or email...') }}"
                type="search"
                icon="magnifying-glass"
            />
        </div>

        {{-- Users Table --}}
        <div class="rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-zinc-800 overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-zinc-900/50">
                        <th class="px-6 py-3 text-left font-medium text-neutral-600 dark:text-neutral-400">{{ __('Name') }}</th>
                        <th class="px-6 py-3 text-left font-medium text-neutral-600 dark:text-neutral-400">{{ __('Email') }}</th>
                        <th class="px-6 py-3 text-left font-medium text-neutral-600 dark:text-neutral-400">{{ __('Role') }}</th>
                        <th class="px-6 py-3 text-left font-medium text-neutral-600 dark:text-neutral-400">{{ __('Active') }}</th>
                        <th class="px-6 py-3 text-left font-medium text-neutral-600 dark:text-neutral-400">{{ __('Phone') }}</th>
                        <th class="px-6 py-3 text-right font-medium text-neutral-600 dark:text-neutral-400">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                    @forelse ($users as $user)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-zinc-700/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-neutral-200 text-xs font-bold text-neutral-700 dark:bg-neutral-700 dark:text-neutral-300">
                                        {{ $user->initials }}
                                    </div>
                                    <span class="font-medium text-neutral-900 dark:text-neutral-100">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-neutral-600 dark:text-neutral-400">{{ $user->email }}</td>
                            <td class="px-6 py-4">
                                @if ($user->role)
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                        style="background-color: {{ $user->role->color }}26; color: {{ $user->role->color }}; border: 1px solid {{ $user->role->color }}4d;">
                                        {{ $user->role->name }}
                                    </span>
                                @else
                                    <span class="text-neutral-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if ($user->is_active)
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-400">true</span>
                                @else
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400">false</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-neutral-600 dark:text-neutral-400">{{ $user->phone_number ?? '—' }}</td>
                            <td class="px-6 py-4">
                                <x-ui.row-actions>
                                    <flux:tooltip content="{{ __('Edit') }}" position="top">
                                        <x-flux.button variant="ghost" size="sm" wire:click="prepareAction('edit', {{ $user->id }})" icon="pencil" />
                                    </flux:tooltip>
                                    @if ($user->is_active)
                                        <flux:tooltip wire:key="toggle-{{ $user->id }}-active" content="{{ __('Deactivate') }}" position="top">
                                            <x-flux.button variant="ghost" size="sm" wire:click="prepareAction('toggle', {{ $user->id }})" icon="pause-circle" class="!text-yellow-600 hover:!text-yellow-700 dark:!text-yellow-400" />
                                        </flux:tooltip>
                                    @else
                                        <flux:tooltip wire:key="toggle-{{ $user->id }}-inactive" content="{{ __('Reactivate') }}" position="top">
                                            <x-flux.button variant="ghost" size="sm" wire:click="prepareAction('toggle', {{ $user->id }})" icon="play-circle" class="!text-green-600 hover:!text-green-700 dark:!text-green-400" />
                                        </flux:tooltip>
                                    @endif
                                    <flux:tooltip content="{{ __('Delete') }}" position="top">
                                        <x-flux.button variant="ghost" size="sm" wire:click="prepareAction('delete', {{ $user->id }})" icon="trash" class="!text-red-600 hover:!text-red-700 dark:!text-red-400" />
                                    </flux:tooltip>
                                </x-ui.row-actions>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-neutral-500 dark:text-neutral-400">
                                {{ __('No users found.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $users->links() }}
        </div>
    </div>

    {{-- Password Confirmation Modal --}}
    <flux:modal name="confirm-action" :show="$showConfirmModal" wire:model="showConfirmModal">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Confirm Action') }}</flux:heading>
                <flux:subheading>{{ __('Please enter your admin password to continue.') }}</flux:subheading>
            </div>

            <flux:input
                wire:model="confirmPassword"
                type="password"
                label="{{ __('Password') }}"
                placeholder="{{ __('Enter your password') }}"
                wire:keydown.enter="executeAction"
                autofocus
            />

            @if ($passwordError)
                <p class="text-sm text-red-600 dark:text-red-400">{{ $passwordError }}</p>
            @endif

            <div class="flex gap-2 justify-end">
                <x-flux.button variant="ghost" wire:click="cancelAction">{{ __('Cancel') }}</x-flux.button>
                <x-flux.button variant="primary" wire:click="executeAction">{{ __('Confirm') }}</x-flux.button>
            </div>
        </div>
    </flux:modal>
</section>

