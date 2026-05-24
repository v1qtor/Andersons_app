{{--
    meal-planning.blade.php: view half of the MealPlanning page
    component. Manager page that embeds MealList together with the
    add-meal and edit-invitees popups.
--}}
<section class="w-full">
    <div class="max-w-5xl mx-auto">
        {{-- Header --}}
        <div class="flex flex-col gap-4 mb-8 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Meal Planning') }}</flux:heading>
                <flux:subheading>{{ __('Manage planned meals for the household') }}</flux:subheading>
            </div>
            {{-- Tells the AddMealModal child to open. --}}
            <x-flux.button variant="primary" wire:click="$dispatch('openAddMeal')" icon="plus" class="sm:self-start">
                {{ __('Add Meal') }}
            </x-flux.button>
        </div>

        {{-- The shared meal list. --}}
        <livewire:meals.meal-list />

        {{-- Add-meal popup. --}}
        <livewire:meals.add-meal-modal />

        {{-- Edit-invitees popup. --}}
        <livewire:meals.edit-invitees-modal />
    </div>
</section>
