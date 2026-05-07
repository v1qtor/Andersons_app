@props([
    'fixed' => false,
    'duration' => 3500,
    'maxWidth' => 'max-w-md',
])

@php
    $message = session('status') ?? session('message') ?? session('error');
    $isError = (bool) session('error');
    $type = $isError ? 'red' : 'green';
    $title = $isError ? 'Error' : 'Success';

    $wrapperClass = $fixed
        ? "fixed top-4 right-4 z-50 w-full {$maxWidth}"
        : 'mb-6';
@endphp

@if($message)
    <div x-data="{ show: true }"
         x-init="setTimeout(() => show = false, {{ (int) $duration }})"
         x-show="show"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 translate-x-0"
         x-transition:leave-end="opacity-0 translate-x-16"
         {{ $attributes->merge(['class' => $wrapperClass]) }}>
        <x-flux.alert :type="$type" :title="$title" class="{{ $fixed ? 'mb-0 shadow-lg' : '' }}">
            {{ $message }}
        </x-flux.alert>
    </div>
@endif
