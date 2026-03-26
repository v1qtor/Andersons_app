<div class="p-4 mb-4 rounded border border-{{ $type ?? 'info' }}-300 bg-{{ $type ?? 'info' }}-50 text-{{ $type ?? 'info' }}-800">
    @if(isset($title))
        <div class="font-bold mb-1">{{ $title }}</div>
    @endif
    <div>{{ $slot }}</div>
</div>