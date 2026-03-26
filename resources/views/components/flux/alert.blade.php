@props([
    'type' => 'info',
    'title' => null,
])

<div {{ $attributes->merge([
    'class' => "p-4 mb-4 rounded border border-{$type}-300 bg-{$type}-50 text-{$type}-800 dark:border-{$type}-800 dark:bg-{$type}-900/20 dark:text-{$type}-300",
]) }}>
    @if($title)
        <div class="font-bold mb-1">{{ $title }}</div>
    @endif
    <div>{{ $slot }}</div>
</div>