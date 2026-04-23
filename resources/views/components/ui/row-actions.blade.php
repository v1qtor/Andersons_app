@props([
    'align' => 'end',
])

@php
    $alignClasses = [
        'start' => 'justify-start',
        'center' => 'justify-center',
        'between' => 'justify-between',
        'end' => 'justify-end',
    ];
@endphp

<div {{ $attributes->class('flex items-center gap-1 ' . ($alignClasses[$align] ?? $alignClasses['end'])) }}>
    {{ $slot }}
</div>
