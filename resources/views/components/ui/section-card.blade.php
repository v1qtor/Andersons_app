@props([
    'padding' => 'p-6',
    'shadow' => true,
])

@php
    $baseClasses = 'rounded-xl border border-gray-200 bg-white dark:border-neutral-700 dark:bg-neutral-800';
    $shadowClasses = $shadow ? 'shadow-sm' : '';
@endphp

<div {{ $attributes->class(trim("{$baseClasses} {$shadowClasses} {$padding}")) }}>
    {{ $slot }}
</div>
