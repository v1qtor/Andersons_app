<section class="w-full">
    <div class="max-w-5xl mx-auto">
        {{-- Header --}}
        <div class="flex flex-col gap-4 mb-8 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Meal Planning') }}</flux:heading>
                <flux:subheading>{{ __('Manage planned meals for the household') }}</flux:subheading>
            </div>
            <x-flux.button variant="primary" wire:click="$dispatch('openAddMeal')" icon="plus" class="sm:self-start">
                {{ __('Add Meal') }}
            </x-flux.button>
        </div>

        {{-- Reusable Meal List Component --}}
        <livewire:meals.meal-list />

        {{-- Reusable Add Meal Modal Component --}}
        <livewire:meals.add-meal-modal />

        {{-- Edit Invitees Modal (Admin/Chef only) --}}
        <livewire:meals.edit-invitees-modal />
    </div>
</section>
