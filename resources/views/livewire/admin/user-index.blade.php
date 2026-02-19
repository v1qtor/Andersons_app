<section class="w-full">
    <div class="max-w-5xl mx-auto">
        {{-- Header --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <flux:heading size="xl">{{ __('Users Management') }}</flux:heading>
                <flux:subheading>{{ __('Manage all users in the system') }}</flux:subheading>
            </div>
            <flux:button variant="primary" :href="route('admin.users.create')" wire:navigate icon="plus">
                {{ __('Create User') }}
            </flux:button>
        </div>

        {{-- Flash Message --}}
        @if (session('message'))
            <div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 text-green-800 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400">
                {{ session('message') }}
            </div>
        @endif

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
        <div class="rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-zinc-800 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-zinc-900/50">
                        <th class="px-6 py-3 text-left font-medium text-neutral-600 dark:text-neutral-400">{{ __('Name') }}</th>
                        <th class="px-6 py-3 text-left font-medium text-neutral-600 dark:text-neutral-400">{{ __('Email') }}</th>
                        <th class="px-6 py-3 text-left font-medium text-neutral-600 dark:text-neutral-400">{{ __('Role') }}</th>
                        <th class="px-6 py-3 text-left font-medium text-neutral-600 dark:text-neutral-400">{{ __('Country') }}</th>
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
                                        {{ $user->initials() }}
                                    </div>
                                    <span class="font-medium text-neutral-900 dark:text-neutral-100">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-neutral-600 dark:text-neutral-400">{{ $user->email }}</td>
                            <td class="px-6 py-4">
                                @if ($user->role)
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400">
                                        {{ $user->role->name }}
                                    </span>
                                @else
                                    <span class="text-neutral-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-neutral-600 dark:text-neutral-400">{{ $user->country?->name ?? '—' }}</td>
                            <td class="px-6 py-4 text-neutral-600 dark:text-neutral-400">{{ $user->phoneNumber ?? '—' }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <flux:button variant="ghost" size="sm" :href="route('admin.users.edit', $user->userId)" wire:navigate icon="pencil">
                                        {{ __('Edit') }}
                                    </flux:button>
                                    <flux:button variant="ghost" size="sm" wire:click="deleteUser({{ $user->userId }})" wire:confirm="{{ __('Are you sure you want to deactivate this user?') }}" icon="trash" class="!text-red-600 hover:!text-red-700 dark:!text-red-400">
                                        {{ __('Deactivate') }}
                                    </flux:button>
                                </div>
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
</section>

