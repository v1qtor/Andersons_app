<section class="w-full">
    <div class="max-w-5xl mx-auto">
        {{-- Header --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <flux:heading size="xl">{{ __('Admin Panel') }}</flux:heading>
                <flux:subheading>{{ __('Manage system settings and data') }}</flux:subheading>
            </div>
        </div>

        {{-- Flash Message --}}
        @if (session('message'))
            <div
                x-data="{ show: true }"
                x-init="setTimeout(() => show = false, 5000)"
                x-show="show"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 text-green-800 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400"
            >
                {{ session('message') }}
            </div>
        @endif

        @if (session('error'))
            <div
                x-data="{ show: true }"
                x-init="setTimeout(() => show = false, 5000)"
                x-show="show"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-red-800 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400"
            >
                {{ session('error') }}
            </div>
        @endif

        {{-- Tab Navigation --}}
        <div class="mb-6 border-b border-neutral-200 dark:border-neutral-700">
            <nav class="flex gap-2 overflow-x-auto">
                <button 
                    wire:click="setTab('users')" 
                    class="px-4 py-3 text-sm font-medium transition-colors whitespace-nowrap cursor-pointer {{ $activeTab === 'users' ? 'border-b-2 border-blue-500 text-blue-600 dark:text-blue-400' : 'text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-100' }}"
                >
                    {{ __('Users') }}
                </button>
                <button 
                    wire:click="setTab('categories')" 
                    class="px-4 py-3 text-sm font-medium transition-colors whitespace-nowrap cursor-pointer {{ $activeTab === 'categories' ? 'border-b-2 border-blue-500 text-blue-600 dark:text-blue-400' : 'text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-100' }}"
                >
                    {{ __('Invoice Categories') }}
                </button>
                <button 
                    wire:click="setTab('task_categories')" 
                    class="px-4 py-3 text-sm font-medium transition-colors whitespace-nowrap cursor-pointer {{ $activeTab === 'task_categories' ? 'border-b-2 border-blue-500 text-blue-600 dark:text-blue-400' : 'text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-100' }}"
                >
                    {{ __('Task Categories') }}
                </button>
                <button 
                    wire:click="setTab('locations')" 
                    class="px-4 py-3 text-sm font-medium transition-colors whitespace-nowrap cursor-pointer {{ $activeTab === 'locations' ? 'border-b-2 border-blue-500 text-blue-600 dark:text-blue-400' : 'text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-100' }}"
                >
                    {{ __('Locations') }}
                </button>
                <button 
                    wire:click="setTab('task_priorities')" 
                    class="px-4 py-3 text-sm font-medium transition-colors whitespace-nowrap cursor-pointer {{ $activeTab === 'task_priorities' ? 'border-b-2 border-blue-500 text-blue-600 dark:text-blue-400' : 'text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-100' }}"
                >
                    {{ __('Task Priorities') }}
                </button>
                <button 
                    wire:click="setTab('role_colors')" 
                    class="px-4 py-3 text-sm font-medium transition-colors whitespace-nowrap cursor-pointer {{ $activeTab === 'role_colors' ? 'border-b-2 border-blue-500 text-blue-600 dark:text-blue-400' : 'text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-100' }}"
                >
                    {{ __('Role Colors') }}
                </button>
            </nav>
        </div>

        {{-- Tab Content --}}
        @if($activeTab === 'users')
            {{-- Users Management Section --}}
            <div class="mb-4 flex items-center justify-end">
                <flux:button variant="primary" :href="route('admin.users.create')" wire:navigate icon="plus">
                    {{ __('Create User') }}
                </flux:button>
            </div>

            {{-- Search --}}
            <div class="mb-6">
                <flux:input
                    wire:model.live.debounce.300ms="search.users"
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
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-400">active</span>
                                    @else
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400">inactive</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-neutral-600 dark:text-neutral-400">{{ $user->phone_number ?? '—' }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-1">
                                        <flux:tooltip content="{{ __('Edit') }}" position="top">
                                            <flux:button variant="ghost" size="sm" wire:click="prepareAction('edit', {{ $user->id }})" icon="pencil" />
                                        </flux:tooltip>
                                        @if ($user->is_active)
                                            <flux:tooltip wire:key="toggle-{{ $user->id }}-active" content="{{ __('Deactivate') }}" position="top">
                                                <flux:button variant="ghost" size="sm" wire:click="prepareAction('toggle', {{ $user->id }})" icon="pause-circle" class="!text-yellow-600 hover:!text-yellow-700 dark:!text-yellow-400" />
                                            </flux:tooltip>
                                        @else
                                            <flux:tooltip wire:key="toggle-{{ $user->id }}-inactive" content="{{ __('Reactivate') }}" position="top">
                                                <flux:button variant="ghost" size="sm" wire:click="prepareAction('toggle', {{ $user->id }})" icon="play-circle" class="!text-green-600 hover:!text-green-700 dark:!text-green-400" />
                                            </flux:tooltip>
                                        @endif
                                        <flux:tooltip content="{{ __('Delete') }}" position="top">
                                            <flux:button variant="ghost" size="sm" wire:click="prepareAction('delete', {{ $user->id }})" icon="trash" class="!text-red-600 hover:!text-red-700 dark:!text-red-400" />
                                        </flux:tooltip>
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
        @endif

        @if($activeTab === 'categories')
            {{-- Invoice Categories Management Section --}}
            <div class="mb-4 flex items-center justify-end">
                <flux:button variant="primary" wire:click="startCreating" icon="plus">
                    {{ __('Add Invoice Category') }}
                </flux:button>
            </div>

            {{-- Search --}}
            <div class="mb-6">
                <flux:input
                    wire:model.live.debounce.300ms="search.categories"
                    placeholder="{{ __('Search invoice categories...') }}"
                    type="search"
                    icon="magnifying-glass"
                />
            </div>

            {{-- Categories Table --}}
            <div class="rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-zinc-800 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-zinc-900/50">
                            <th class="px-6 py-3 text-left font-medium text-neutral-600 dark:text-neutral-400">{{ __('Name') }}</th>
                            <th class="px-6 py-3 text-right font-medium text-neutral-600 dark:text-neutral-400">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                        @if($creatingNew)
                            <tr class="bg-blue-50 dark:bg-blue-900/20">
                                <td class="px-6 py-4">
                                    <flux:input
                                        wire:model="newEntityName"
                                        placeholder="{{ __('Enter invoice category name...') }}"
                                        wire:keydown.enter="saveNew('categories')"
                                        wire:keydown.escape="cancelCreating"
                                        autofocus
                                    />
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-1">
                                        <flux:button variant="primary" size="sm" wire:click="saveNew('categories')" icon="check" />
                                        <flux:button variant="ghost" size="sm" wire:click="cancelCreating" icon="x-mark" />
                                    </div>
                                </td>
                            </tr>
                        @endif
                        @forelse ($categories as $category)
                            <tr class="hover:bg-neutral-50 dark:hover:bg-zinc-700/50 transition-colors">
                                <td class="px-6 py-4">
                                    @if($editingId === $category->id)
                                        <flux:input
                                            wire:model="editingValue"
                                            wire:keydown.enter="saveEdit('categories', {{ $category->id }})"
                                            wire:keydown.escape="cancelEditing"
                                            autofocus
                                        />
                                    @else
                                        <span class="font-medium text-neutral-900 dark:text-neutral-100">{{ $category->name }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-1">
                                        @if($editingId === $category->id)
                                            <flux:button variant="primary" size="sm" wire:click="saveEdit('categories', {{ $category->id }})" icon="check" />
                                            <flux:button variant="ghost" size="sm" wire:click="cancelEditing" icon="x-mark" />
                                        @else
                                            <flux:tooltip content="{{ __('Edit') }}" position="top">
                                                <flux:button variant="ghost" size="sm" wire:click="startEditing({{ $category->id }}, '{{ addslashes($category->name) }}')" icon="pencil" />
                                            </flux:tooltip>
                                            <flux:tooltip content="{{ __('Delete') }}" position="top">
                                                <flux:button variant="ghost" size="sm" wire:click="prepareDelete('categories', {{ $category->id }})" icon="trash" class="!text-red-600 hover:!text-red-700 dark:!text-red-400" />
                                            </flux:tooltip>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            @if(!$creatingNew)
                                <tr>
                                    <td colspan="2" class="px-6 py-12 text-center text-neutral-500 dark:text-neutral-400">
                                        {{ __('No invoice categories found.') }}
                                    </td>
                                </tr>
                            @endif
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $categories->links() }}
            </div>
        @endif

        @if($activeTab === 'task_categories')
            {{-- Task Categories Management Section --}}
            <div class="mb-4 flex items-center justify-end">
                <flux:button variant="primary" wire:click="startCreating" icon="plus">
                    {{ __('Add Task Category') }}
                </flux:button>
            </div>

            {{-- Search --}}
            <div class="mb-6">
                <flux:input
                    wire:model.live.debounce.300ms="search.task_categories"
                    placeholder="{{ __('Search task categories...') }}"
                    type="search"
                    icon="magnifying-glass"
                />
            </div>

            {{-- Task Categories Table --}}
            <div class="rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-zinc-800 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-zinc-900/50">
                            <th class="px-6 py-3 text-left font-medium text-neutral-600 dark:text-neutral-400">{{ __('Name') }}</th>
                            <th class="px-6 py-3 text-right font-medium text-neutral-600 dark:text-neutral-400">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                        @if($creatingNew)
                            <tr class="bg-blue-50 dark:bg-blue-900/20">
                                <td class="px-6 py-4">
                                    <flux:input
                                        wire:model="newEntityName"
                                        placeholder="{{ __('Enter task category name...') }}"
                                        wire:keydown.enter="saveNew('task_categories')"
                                        wire:keydown.escape="cancelCreating"
                                        autofocus
                                    />
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-1">
                                        <flux:button variant="primary" size="sm" wire:click="saveNew('task_categories')" icon="check" />
                                        <flux:button variant="ghost" size="sm" wire:click="cancelCreating" icon="x-mark" />
                                    </div>
                                </td>
                            </tr>
                        @endif
                        @forelse ($taskCategories as $taskCategory)
                            <tr class="hover:bg-neutral-50 dark:hover:bg-zinc-700/50 transition-colors">
                                <td class="px-6 py-4">
                                    @if($editingId === $taskCategory->id)
                                        <flux:input
                                            wire:model="editingValue"
                                            wire:keydown.enter="saveEdit('task_categories', {{ $taskCategory->id }})"
                                            wire:keydown.escape="cancelEditing"
                                            autofocus
                                        />
                                    @else
                                        <span class="font-medium text-neutral-900 dark:text-neutral-100">{{ $taskCategory->name }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-1">
                                        @if($editingId === $taskCategory->id)
                                            <flux:button variant="primary" size="sm" wire:click="saveEdit('task_categories', {{ $taskCategory->id }})" icon="check" />
                                            <flux:button variant="ghost" size="sm" wire:click="cancelEditing" icon="x-mark" />
                                        @else
                                            <flux:tooltip content="{{ __('Edit') }}" position="top">
                                                <flux:button variant="ghost" size="sm" wire:click="startEditing({{ $taskCategory->id }}, '{{ addslashes($taskCategory->name) }}')" icon="pencil" />
                                            </flux:tooltip>
                                            <flux:tooltip content="{{ __('Delete') }}" position="top">
                                                <flux:button variant="ghost" size="sm" wire:click="prepareDelete('task_categories', {{ $taskCategory->id }})" icon="trash" class="!text-red-600 hover:!text-red-700 dark:!text-red-400" />
                                            </flux:tooltip>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            @if(!$creatingNew)
                                <tr>
                                    <td colspan="2" class="px-6 py-12 text-center text-neutral-500 dark:text-neutral-400">
                                        {{ __('No task categories found.') }}
                                    </td>
                                </tr>
                            @endif
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $taskCategories->links() }}
            </div>
        @endif

        @if($activeTab === 'locations')
            {{-- Locations Management Section --}}
            <div class="mb-4 flex items-center justify-end">
                <flux:button variant="primary" wire:click="startCreating" icon="plus">
                    {{ __('Add Location') }}
                </flux:button>
            </div>

            {{-- Search --}}
            <div class="mb-6">
                <flux:input
                    wire:model.live.debounce.300ms="search.locations"
                    placeholder="{{ __('Search locations...') }}"
                    type="search"
                    icon="magnifying-glass"
                />
            </div>

            {{-- Locations Table --}}
            <div class="rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-zinc-800 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-zinc-900/50">
                            <th class="px-6 py-3 text-left font-medium text-neutral-600 dark:text-neutral-400">{{ __('Name') }}</th>
                            <th class="px-6 py-3 text-right font-medium text-neutral-600 dark:text-neutral-400">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                        @if($creatingNew)
                            <tr class="bg-blue-50 dark:bg-blue-900/20">
                                <td class="px-6 py-4">
                                    <flux:input
                                        wire:model="newEntityName"
                                        placeholder="{{ __('Enter location name...') }}"
                                        wire:keydown.enter="saveNew('locations')"
                                        wire:keydown.escape="cancelCreating"
                                        autofocus
                                    />
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-1">
                                        <flux:button variant="primary" size="sm" wire:click="saveNew('locations')" icon="check" />
                                        <flux:button variant="ghost" size="sm" wire:click="cancelCreating" icon="x-mark" />
                                    </div>
                                </td>
                            </tr>
                        @endif
                        @forelse ($locations as $location)
                            <tr class="hover:bg-neutral-50 dark:hover:bg-zinc-700/50 transition-colors">
                                <td class="px-6 py-4">
                                    @if($editingId === $location->id)
                                        <flux:input
                                            wire:model="editingValue"
                                            wire:keydown.enter="saveEdit('locations', {{ $location->id }})"
                                            wire:keydown.escape="cancelEditing"
                                            autofocus
                                        />
                                    @else
                                        <span class="font-medium text-neutral-900 dark:text-neutral-100">{{ $location->name }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-1">
                                        @if($editingId === $location->id)
                                            <flux:button variant="primary" size="sm" wire:click="saveEdit('locations', {{ $location->id }})" icon="check" />
                                            <flux:button variant="ghost" size="sm" wire:click="cancelEditing" icon="x-mark" />
                                        @else
                                            <flux:tooltip content="{{ __('Edit') }}" position="top">
                                                <flux:button variant="ghost" size="sm" wire:click="startEditing({{ $location->id }}, '{{ addslashes($location->name) }}')" icon="pencil" />
                                            </flux:tooltip>
                                            <flux:tooltip content="{{ __('Delete') }}" position="top">
                                                <flux:button variant="ghost" size="sm" wire:click="prepareDelete('locations', {{ $location->id }})" icon="trash" class="!text-red-600 hover:!text-red-700 dark:!text-red-400" />
                                            </flux:tooltip>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            @if(!$creatingNew)
                                <tr>
                                    <td colspan="2" class="px-6 py-12 text-center text-neutral-500 dark:text-neutral-400">
                                        {{ __('No locations found.') }}
                                    </td>
                                </tr>
                            @endif
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $locations->links() }}
            </div>
        @endif

        @if($activeTab === 'task_priorities')
            {{-- Task Priorities Management Section --}}
            <div class="mb-4 flex items-center justify-end">
                <flux:button variant="primary" wire:click="startCreating" icon="plus">
                    {{ __('Add Task Priority') }}
                </flux:button>
            </div>

            {{-- Search --}}
            <div class="mb-6">
                <flux:input
                    wire:model.live.debounce.300ms="search.task_priorities"
                    placeholder="{{ __('Search task priorities...') }}"
                    type="search"
                    icon="magnifying-glass"
                />
            </div>

            {{-- Task Priorities Table --}}
            <div class="rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-zinc-800 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-zinc-900/50">
                            <th class="px-6 py-3 text-left font-medium text-neutral-600 dark:text-neutral-400">{{ __('Name') }}</th>
                            <th class="px-6 py-3 text-right font-medium text-neutral-600 dark:text-neutral-400">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                        @if($creatingNew)
                            <tr class="bg-blue-50 dark:bg-blue-900/20">
                                <td class="px-6 py-4">
                                    <flux:input
                                        wire:model="newEntityName"
                                        placeholder="{{ __('Enter task priority name...') }}"
                                        wire:keydown.enter="saveNew('task_priorities')"
                                        wire:keydown.escape="cancelCreating"
                                        autofocus
                                    />
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-1">
                                        <flux:button variant="primary" size="sm" wire:click="saveNew('task_priorities')" icon="check" />
                                        <flux:button variant="ghost" size="sm" wire:click="cancelCreating" icon="x-mark" />
                                    </div>
                                </td>
                            </tr>
                        @endif
                        @forelse ($taskPriorities as $taskPriority)
                            <tr class="hover:bg-neutral-50 dark:hover:bg-zinc-700/50 transition-colors">
                                <td class="px-6 py-4">
                                    @if($editingId === $taskPriority->id)
                                        <flux:input
                                            wire:model="editingValue"
                                            wire:keydown.enter="saveEdit('task_priorities', {{ $taskPriority->id }})"
                                            wire:keydown.escape="cancelEditing"
                                            autofocus
                                        />
                                    @else
                                        <span class="font-medium text-neutral-900 dark:text-neutral-100">{{ $taskPriority->name }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-1">
                                        @if($editingId === $taskPriority->id)
                                            <flux:button variant="primary" size="sm" wire:click="saveEdit('task_priorities', {{ $taskPriority->id }})" icon="check" />
                                            <flux:button variant="ghost" size="sm" wire:click="cancelEditing" icon="x-mark" />
                                        @else
                                            <flux:tooltip content="{{ __('Edit') }}" position="top">
                                                <flux:button variant="ghost" size="sm" wire:click="startEditing({{ $taskPriority->id }}, '{{ addslashes($taskPriority->name) }}')" icon="pencil" />
                                            </flux:tooltip>
                                            <flux:tooltip content="{{ __('Delete') }}" position="top">
                                                <flux:button variant="ghost" size="sm" wire:click="prepareDelete('task_priorities', {{ $taskPriority->id }})" icon="trash" class="!text-red-600 hover:!text-red-700 dark:!text-red-400" />
                                            </flux:tooltip>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            @if(!$creatingNew)
                                <tr>
                                    <td colspan="2" class="px-6 py-12 text-center text-neutral-500 dark:text-neutral-400">
                                        {{ __('No task priorities found.') }}
                                    </td>
                                </tr>
                            @endif
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $taskPriorities->links() }}
            </div>
        @endif

        @if($activeTab === 'role_colors')
            {{-- Role Colors Management Section --}}
            {{-- Search --}}
            <div class="mb-6">
                <flux:input
                    wire:model.live.debounce.300ms="search.role_colors"
                    placeholder="{{ __('Search roles...') }}"
                    type="search"
                    icon="magnifying-glass"
                />
            </div>

            {{-- Role Colors Table --}}
            <div class="rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-zinc-800 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-zinc-900/50">
                            <th class="px-6 py-3 text-left font-medium text-neutral-600 dark:text-neutral-400">{{ __('Role Name') }}</th>
                            <th class="px-6 py-3 text-left font-medium text-neutral-600 dark:text-neutral-400">{{ __('Color Preview') }}</th>
                            <th class="px-6 py-3 text-right font-medium text-neutral-600 dark:text-neutral-400">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                        @forelse ($roles as $role)
                            <tr class="hover:bg-neutral-50 dark:hover:bg-zinc-700/50 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="font-medium text-neutral-900 dark:text-neutral-100">{{ $role->name }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($editingColorRoleId === $role->id)
                                        <div class="flex items-center gap-3" x-data="{ previewColor: @entangle('editingColor') }">
                                            <input 
                                                type="color" 
                                                wire:model.live="editingColor"
                                                x-model="previewColor"
                                                class="h-10 w-20 rounded border border-neutral-300 dark:border-neutral-600 cursor-pointer"
                                            />
                                            <span 
                                                class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium"
                                                :style="`background-color: ${previewColor}26; color: ${previewColor}; border: 1px solid ${previewColor}4d;`"
                                            >
                                                {{ $role->name }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium"
                                            style="background-color: {{ $role->color }}26; color: {{ $role->color }}; border: 1px solid {{ $role->color }}4d;">
                                            {{ $role->name }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-1">
                                        @if($editingColorRoleId === $role->id)
                                            <flux:button variant="primary" size="sm" wire:click="saveColor({{ $role->id }})" icon="check" />
                                            <flux:button variant="ghost" size="sm" wire:click="cancelEditingColor" icon="x-mark" />
                                        @else
                                            <flux:tooltip content="{{ __('Edit Color') }}" position="top">
                                                <flux:button variant="ghost" size="sm" wire:click="startEditingColor({{ $role->id }}, '{{ $role->color }}')" icon="pencil" />
                                            </flux:tooltip>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-12 text-center text-neutral-500 dark:text-neutral-400">
                                    {{ __('No roles found.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
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
                <flux:button variant="ghost" wire:click="cancelAction">{{ __('Cancel') }}</flux:button>
                <flux:button variant="primary" wire:click="executeAction">{{ __('Confirm') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</section>
