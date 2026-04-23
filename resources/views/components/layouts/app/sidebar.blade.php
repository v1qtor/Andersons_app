<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-white dark:bg-zinc-800" data-user-id="{{ auth()->id() }}">
<flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
    <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

    <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
        <x-app-logo />
    </a>

    <flux:navlist variant="outline">
                <flux:navlist.group :heading="__('Pages')" class="grid">
                    <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>
                    <flux:navlist.item icon="calendar-days" :href="route('schedule')" :current="request()->routeIs('schedule')" wire:navigate>{{ __('Schedule') }}</flux:navlist.item>
                    <flux:navlist.item icon="cog" :href="route('settings')" :current="request()->routeIs('settings')" wire:navigate>{{ __('Settings') }}</flux:navlist.item>
                    @if(auth()->user()->role && in_array(auth()->user()->role->name, ['Staff', 'Chef', 'Admin', 'The Andersons']))
                        <flux:navlist.item icon="document-text" :href="route('invoices')" :current="request()->routeIs('invoices*')" wire:navigate>{{ __('Invoices') }}</flux:navlist.item>
                    @endif
                    @if(auth()->user()->role && auth()->user()->role->name === 'Admin')
                        <flux:navlist.item icon="cog-6-tooth" :href="route('admin.panel')" :current="request()->routeIs('admin.panel') || request()->routeIs('admin.users.*')" wire:navigate>{{ __('Admin Panel') }}</flux:navlist.item>
                        <flux:navlist.item icon="fire" :href="route('admin.meals.index')" :current="request()->routeIs('admin.meals.*')" wire:navigate>{{ __('Meals') }}</flux:navlist.item>
                    @endif
                    @if(auth()->user()->role && auth()->user()->role->name === 'Chef')
                        <flux:navlist.item icon="fire" :href="route('chef.meals.index')" :current="request()->routeIs('chef.meals.*')" wire:navigate>{{ __('Meals') }}</flux:navlist.item>
                    @endif
                    @if(auth()->user()->role && in_array(auth()->user()->role->name, ['Family Member', 'The Andersons', 'Staff']))
                        <flux:navlist.item icon="fire" :href="route('meals.index')" :current="request()->routeIs('meals.index')" wire:navigate>{{ __('Meals') }}</flux:navlist.item>
                    @endif
                </flux:navlist.group>
    </flux:navlist>

    <flux:spacer />

    @auth
    <div class="hidden lg:block border-t border-b border-zinc-200 bg-zinc-100 pt-3 pb-3 -mx-4 dark:border-zinc-700 dark:bg-zinc-800">
        <!-- Desktop Notification Bell -->
        <div class="flex justify-center mb-3 px-4">
            <x-notifications.bell-desktop />
        </div>

        <div class="flex items-center gap-3 px-4 py-1.5">
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-neutral-200 text-sm font-semibold text-black">
                {{ auth()->user()->initials }}
            </span>
            <div class="grid flex-1 text-start text-sm leading-tight min-w-0">
                <span class="truncate font-semibold text-gray-900 dark:text-white">{{ auth()->user()->name }}</span>
                <span class="truncate text-xs font-medium" style="color: {{ auth()->user()->role?->color ?? '#9ca3af' }}">
                    {{ auth()->user()->role?->name ?? 'No Role' }}
                </span>
            </div>
        </div>
        <div class="flex justify-center pt-3">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex items-center gap-2 text-base text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 transition-colors cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                    Logout
                </button>
            </form>
        </div>
    </div>
    @endauth
</flux:sidebar>

<flux:header class="lg:hidden">
    <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

    <flux:spacer />

    @auth
    <x-notifications.bell />
    @endauth
    
    <flux:spacer />

    @auth
    <flux:dropdown position="top" align="end">
        <flux:profile
            :initials="auth()->user()->initials"
            icon-trailing="chevron-down"
        />

        <flux:menu>
            <flux:menu.radio.group>
                <div class="p-0 text-sm font-normal">
                    <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials }}
                                    </span>
                                </span>

                        <div class="grid flex-1 text-start text-sm leading-tight">
                            <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                            <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                            <span class="truncate text-xs font-medium mt-0.5" style="color: {{ auth()->user()->role?->color ?? '#9ca3af' }}">
                                        {{ auth()->user()->role?->name ?? 'No Role' }}
                                    </span>
                        </div>
                    </div>
                </div>
            </flux:menu.radio.group>

            <flux:menu.separator />

            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                    {{ __('Log Out') }}
                </flux:menu.item>
            </form>
        </flux:menu>
    </flux:dropdown>
    @endauth
</flux:header>

{{ $slot }}

<!-- Toast Notifications Container -->
<x-toast-container />

@fluxScripts
</body>
</html>
