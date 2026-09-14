@props([
    'checkpoints' => [], // ordered list of ['location' => string, 'lat' => float, 'lng' => float]
    'height' => '320px',
])

@php
    $apiKey = config('services.google_maps.key');
    $points = collect($checkpoints)->values();
    $coords = $points->map(fn ($c) => $c['lat'] . ',' . $c['lng']);

    $embedUrl = null;
    $openUrl = null;

    if ($apiKey && $points->count() === 1) {
        $embedUrl = 'https://www.google.com/maps/embed/v1/place?key=' . $apiKey . '&q=' . urlencode($coords->first());
        $openUrl = 'https://www.google.com/maps/search/?api=1&query=' . urlencode($coords->first());
    } elseif ($apiKey && $points->count() >= 2) {
        $origin = $coords->first();
        $destination = $coords->last();
        $waypoints = $coords->slice(1, -1);

        $embedUrl = 'https://www.google.com/maps/embed/v1/directions?key=' . $apiKey
            . '&origin=' . urlencode($origin)
            . '&destination=' . urlencode($destination)
            . ($waypoints->isNotEmpty() ? '&waypoints=' . urlencode($waypoints->implode('|')) : '');

        $openUrl = 'https://www.google.com/maps/dir/?api=1'
            . '&origin=' . urlencode($origin)
            . '&destination=' . urlencode($destination)
            . ($waypoints->isNotEmpty() ? '&waypoints=' . urlencode($waypoints->implode('|')) : '');
    }
@endphp

<div {{ $attributes->class('rounded-lg overflow-hidden border border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-900') }}>
    @if (! $apiKey)
        <div class="flex items-center justify-center text-sm text-gray-500 dark:text-gray-400 p-4 text-center" style="height: {{ $height }}">
            Map unavailable (no Google Maps API key configured)
        </div>
    @elseif (! $embedUrl)
        <div class="flex items-center justify-center text-sm text-gray-500 dark:text-gray-400 p-4 text-center" style="height: {{ $height }}">
            Add coordinates to a checkpoint to see the route map
        </div>
    @else
        <iframe
            src="{{ $embedUrl }}"
            width="100%"
            frameborder="0"
            style="border:0; height: {{ $height }}; width: 100%;"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
        ></iframe>
        <div class="px-3 py-2 bg-white dark:bg-neutral-800 border-t border-gray-200 dark:border-neutral-700">
            <a href="{{ $openUrl }}" target="_blank" rel="noopener" class="text-sm text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 underline inline-flex items-center gap-1">
                Open full route in Google Maps ↗
            </a>
        </div>
    @endif
</div>
