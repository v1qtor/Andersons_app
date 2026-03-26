<section class="w-full">
    <div class="max-w-3xl mx-auto">
        {{-- Header --}}
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-2">
                <x-flux.button variant="ghost" size="sm" :href="route('admin.users.index')" wire:navigate icon="arrow-left">
                    {{ __('Back') }}
                </x-flux.button>
            </div>
            <flux:heading size="xl">{{ __('Create User') }}</flux:heading>
            <flux:subheading>{{ __('Add a new user to the system') }}</flux:subheading>
        </div>

        {{-- Form Card --}}
        <div class="rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-zinc-800 p-6">
            <form wire:submit="save">
                <x-admin.user-form :isEdit="false" :roles="$roles" />

                <div class="flex items-center justify-end gap-3 mt-8 pt-6 border-t border-neutral-200 dark:border-neutral-700">
                    <x-flux.button variant="ghost" :href="route('admin.users.index')" wire:navigate>
                        {{ __('Cancel') }}
                    </x-flux.button>
                    <x-flux.button variant="primary" type="submit">
                        {{ __('Create User') }}
                    </x-flux.button>
                </div>
            </form>
        </div>
    </div>
</section>

