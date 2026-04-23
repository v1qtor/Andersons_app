@props([
    'paid' => false,
    'size' => 'md',
])

@php
    $sizeClasses = [
        'sm' => 'px-3 py-1 text-sm',
        'md' => 'px-4 py-2 text-lg',
    ];

    $toneClasses = $paid
        ? 'bg-green-200 text-green-900 dark:bg-green-800 dark:text-green-100'
        : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400';

    $label = trim((string) $slot) !== '' ? $slot : ($paid ? '✓ Paid' : 'Pending');
@endphp

<span {{ $attributes->class('inline-flex items-center rounded-full font-bold ' . ($sizeClasses[$size] ?? $sizeClasses['md']) . ' ' . $toneClasses) }}>
    {{ $label }}
</span>
