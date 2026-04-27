@props([
    'variant' => 'primary',
    'size' => null,
    'icon' => null,
    'type' => null,
    'href' => null,
])

<flux:button
    :variant="$variant"
    :size="$size"
    :icon="$icon"
    :type="$type"
    :href="$href"
    {{ $attributes }}
>
    {{ $slot }}
</flux:button>