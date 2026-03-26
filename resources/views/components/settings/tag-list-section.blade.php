@props([
    'title',
    'items',
    'emptyMessage',
    'currentLabel',
    'addLabel',
    'placeholder',
    'wireModel',
    'addAction',
    'removeMethod',
    'keyPrefix',
    'errorField',
    'modalTitle',
    'contextLabel',
    'color' => 'indigo',
])

@php
$colorMap = [
    'indigo' => [
        'tag'    => 'border-indigo-200 bg-indigo-50 text-indigo-700 dark:border-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300',
        'hover'  => 'hover:bg-indigo-200 dark:hover:bg-indigo-700',
        'ring'   => 'focus:ring-indigo-500 focus:border-indigo-500',
        'button' => 'bg-indigo-600 hover:bg-indigo-700',
    ],
    'emerald' => [
        'tag'    => 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
        'hover'  => 'hover:bg-emerald-200 dark:hover:bg-emerald-700',
        'ring'   => 'focus:ring-emerald-500 focus:border-emerald-500',
        'button' => 'bg-emerald-600 hover:bg-emerald-700',
    ],
];
$c = $colorMap[$color];
@endphp

<div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-gray-200 dark:border-neutral-700 p-8">
    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">{{ $title }}</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Current Items -->
        <div>
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">{{ $currentLabel }}</h3>
            @if ($items->isEmpty())
                <p class="text-sm text-gray-400 dark:text-gray-500">{{ $emptyMessage }}</p>
            @else
                <div class="flex flex-wrap gap-2">
                    @foreach ($items as $item)
                        <span
                            wire:key="{{ $keyPrefix }}-{{ $item->id }}"
                            class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-sm font-medium {{ $c['tag'] }}"
                        >
                            {{ $item->name }}
                            <button
                                type="button"
                                @click="openModal({{ $item->id }}, '{{ addslashes($item->name) }}', '{{ $removeMethod }}', '{{ $modalTitle }}', '{{ $contextLabel }}')"
                                class="flex items-center justify-center w-6 h-6 rounded-full transition-colors {{ $c['hover'] }}"
                                aria-label="Remove {{ $item->name }}"
                            >&times;</button>
                        </span>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Add New Item -->
        <div>
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">{{ $addLabel }}</h3>
            <div class="flex gap-2">
                <input
                    wire:model="{{ $wireModel }}"
                    wire:keydown.enter.prevent="{{ $addAction }}"
                    type="text"
                    placeholder="{{ $placeholder }}"
                    class="flex-1 px-4 py-2.5 border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white rounded-lg focus:ring-2 transition-colors text-sm {{ $c['ring'] }}"
                >
                <button
                    wire:click="{{ $addAction }}"
                    type="button"
                    class="px-4 py-2.5 text-white rounded-lg transition-colors font-semibold text-sm whitespace-nowrap {{ $c['button'] }}"
                >+ Add</button>
            </div>
            @error($errorField)
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
