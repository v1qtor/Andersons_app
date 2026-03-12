<div class="rounded shadow p-4 bg-white border border-gray-200">
    @if(isset($title))
        <div class="font-bold text-lg mb-2">{{ $title }}</div>
    @endif
    <div>{{ $slot }}</div>
</div>