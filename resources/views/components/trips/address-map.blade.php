@props([
    'address' => null,
    'height' => '200px',
])

@php
    $apiKey = config('services.google_maps.key');
    $address = trim((string) $address);
@endphp

<div {{ $attributes->class('rounded-lg overflow-hidden border border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-900') }}>
    @if (! $apiKey)
        <div class="flex items-center justify-center text-sm text-gray-500 dark:text-gray-400 p-4" style="height: {{ $height }}">
            Map preview unavailable (no Google Maps API key configured)
        </div>
    @elseif ($address === '')
        <div class="flex items-center justify-center text-sm text-gray-500 dark:text-gray-400 p-4" style="height: {{ $height }}">
            Enter an address above to preview it on the map
        </div>
    @else
        <iframe
            src="https://www.google.com/maps/embed/v1/place?key={{ $apiKey }}&q={{ urlencode($address) }}"
            width="100%"
            frameborder="0"
            style="border:0; height: {{ $height }}; width: 100%;"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
        ></iframe>
        <div class="px-3 py-2 bg-white dark:bg-neutral-800 border-t border-gray-200 dark:border-neutral-700">
            <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($address) }}" target="_blank" rel="noopener" class="text-sm text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 underline inline-flex items-center gap-1">
                Open in Google Maps ↗
            </a>
        </div>
    @endif
</div>
