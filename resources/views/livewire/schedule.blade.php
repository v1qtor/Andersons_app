<section class="w-full">
    <div class="max-w-7xl mx-auto">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
            <div>
                <flux:heading size="xl">{{ __('Schedule') }}</flux:heading>
                <flux:subheading>{{ __('Household calendar with tasks, meals and trips') }}</flux:subheading>
            </div>

            {{-- View Toggle --}}
            <div class="flex items-center gap-2">
                <flux:button size="sm" :variant="$view === 'day' ? 'primary' : 'ghost'" wire:click="setView('day')">
                    {{ __('Day') }}
                </flux:button>
                <flux:button size="sm" :variant="$view === 'week' ? 'primary' : 'ghost'" wire:click="setView('week')">
                    {{ __('Week') }}
                </flux:button>
                <flux:button size="sm" :variant="$view === 'month' ? 'primary' : 'ghost'" wire:click="setView('month')">
                    {{ __('Month') }}
                </flux:button>
            </div>
        </div>

        {{-- Navigation + Period Label --}}
        <div class="rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-zinc-800 p-6 mb-6">
            <div class="flex items-center justify-between mb-6">
                <flux:button variant="ghost" size="sm" wire:click="previousPeriod" icon="chevron-left" />

                <div class="flex items-center gap-3">
                    <h3 class="text-xl font-bold text-neutral-900 dark:text-neutral-100">
                        {{ $this->periodLabel }}
                    </h3>
                    <flux:button variant="ghost" size="sm" wire:click="goToToday">
                        {{ __('Today') }}
                    </flux:button>
                </div>

                <flux:button variant="ghost" size="sm" wire:click="nextPeriod" icon="chevron-right" />
            </div>
        </div>
    </div>
</section>

