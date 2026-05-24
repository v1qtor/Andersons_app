{{--
    meal-schedule.blade.php: view half of the MealSchedule page
    component. Attendee page that embeds only the shared MealList.
--}}
<section class="w-full">
    <div class="max-w-5xl mx-auto">
        {{-- Header --}}
        <div class="mb-8">
            <flux:heading size="xl">{{ __('Scheduled Meals') }}</flux:heading>
            <flux:subheading>{{ __('View and confirm your participation in upcoming meals') }}</flux:subheading>
        </div>

        {{-- Shared meal list, rendered here in attendee mode. --}}
        <livewire:meals.meal-list />
    </div>
</section>
