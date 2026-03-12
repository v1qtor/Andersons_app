<section class="w-full">
    <div class="max-w-5xl mx-auto">
        {{-- Header --}}
        <div class="mb-8">
            <flux:heading size="xl">{{ __('Scheduled Meals') }}</flux:heading>
            <flux:subheading>{{ __('View and confirm your participation in upcoming meals') }}</flux:subheading>
        </div>

        {{-- Reusable Meal List Component --}}
        <livewire:meals.meal-list />
    </div>
</section>
