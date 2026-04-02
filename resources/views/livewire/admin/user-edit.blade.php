<section class="w-full">
    <div class="max-w-3xl mx-auto">
        {{-- Header --}}
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-2">
                <flux:button variant="ghost" size="sm" :href="route('admin.panel')" wire:navigate icon="arrow-left">
                    {{ __('Back') }}
                </flux:button>
            </div>
            <flux:heading size="xl">{{ __('Edit User') }}</flux:heading>
            <flux:subheading>{{ __('Update user information for :name', ['name' => $user->name]) }}</flux:subheading>
        </div>

        {{-- Flash Message --}}
        @if (session('message'))
            <div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 text-green-800 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400">
                {{ session('message') }}
            </div>
        @endif

        {{-- Form Card --}}
        <div class="rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-zinc-800 p-6">
            <form wire:submit="save">
                <x-admin.user-form :isEdit="true" :roles="$roles" />

                <div class="flex items-center justify-end gap-3 mt-8 pt-6 border-t border-neutral-200 dark:border-neutral-700">
                    <flux:button variant="ghost" :href="route('admin.panel')" wire:navigate>
                        {{ __('Cancel') }}
                    </flux:button>
                    <flux:button variant="primary" type="submit">
                        {{ __('Update User') }}
                    </flux:button>
                </div>
            </form>
        </div>
    </div>
</section>

