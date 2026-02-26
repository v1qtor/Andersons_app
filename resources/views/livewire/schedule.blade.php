<section class="w-full">
    <div class="max-w-7xl mx-auto">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
            <div>
                <flux:heading size="xl">{{ __('Schedule') }}</flux:heading>
                <flux:subheading>{{ __('Household calendar with tasks, meals and trips') }}</flux:subheading>
            </div>
        </div>

        {{-- Reusable Calendar Component --}}
        <livewire:schedule.schedule-calendar />
    </div>
</section>
