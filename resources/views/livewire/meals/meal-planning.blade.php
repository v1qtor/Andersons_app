<section class="w-full">
    <div class="max-w-5xl mx-auto">
        {{-- Header --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <flux:heading size="xl">{{ __('Meal Planning') }}</flux:heading>
                <flux:subheading>{{ __('Manage planned meals for the household') }}</flux:subheading>
            </div>
            <x-flux.button variant="primary" wire:click="$dispatch('openAddMeal')" icon="plus">
                {{ __('Add Meal') }}
            </x-flux.button>
        </div>

        {{-- Reusable Meal List Component --}}
        <livewire:meals.meal-list />

        {{-- Reusable Add Meal Modal Component --}}
        <livewire:meals.add-meal-modal />
    </div>
</section>
