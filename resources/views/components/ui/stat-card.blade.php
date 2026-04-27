@props([
    'label' => '',
    'tone' => 'neutral',
])

@php
    $valueClass = match ($tone) {
        'yellow' => 'text-yellow-600 dark:text-yellow-400',
        'green' => 'text-green-700 dark:text-green-400',
        'red' => 'text-red-600 dark:text-red-400',
        'blue' => 'text-blue-600 dark:text-blue-400',
        default => 'text-gray-900 dark:text-white',
    };
@endphp

<div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-gray-200 dark:border-neutral-700 p-6">
    <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">{{ $label }}</p>
    <p class="text-3xl font-bold {{ $valueClass }}">{{ $slot }}</p>
</div>
