@props([
    'enabled' => false,
    'id' => null,
    'wire' => null,
])

@php
    $isEnabled = (bool) $enabled;
@endphp

<button 
    @if($wire) wire:click="{{ $wire }}" @endif
    @if($id) id="{{ $id }}" @endif
    class="relative inline-flex h-6 w-11 items-center rounded-full border transition-all duration-200 outline-none focus-visible:ring-[3px] {{ $isEnabled ? 'bg-indigo-600 border-transparent focus-visible:ring-indigo-500/50' : 'bg-gray-200 border-gray-300 dark:bg-neutral-700 dark:border-neutral-600 focus-visible:ring-gray-400/50' }}"
>
    <span class="inline-block h-5 w-5 transform rounded-full transition-transform duration-200 pointer-events-none {{ $isEnabled ? 'translate-x-5 bg-white' : 'translate-x-0.5 bg-gray-50 dark:bg-gray-300' }}" />
</button>
