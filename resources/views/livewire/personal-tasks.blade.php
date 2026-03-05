<section class="w-full">
    <div class="max-w-7xl mx-auto">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
            <div>
                <flux:heading size="xl">{{ __('Personal Tasks') }}</flux:heading>
                <flux:subheading>{{ __('Manage your own to-do items') }}</flux:subheading>
            </div>
        </div>

        {{-- Reusable Personal Task Calendar Component --}}
        <livewire:personal-tasks.personal-task-calendar />
    </div>
</section>

