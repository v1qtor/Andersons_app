<div x-cloak x-show="show" @keydown.escape.window="show = false" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-lg mx-4" @click.away="show = false">
        <div class="flex justify-between items-center border-b px-4 py-2">
            <h2 class="text-lg font-semibold">{{ $title ?? '' }}</h2>
            <button @click="show = false" class="text-gray-400 hover:text-gray-700">&times;</button>
        </div>
        <div class="p-4">
            {{ $slot }}
        </div>
        @if(isset($footer))
            <div class="border-t px-4 py-2 bg-gray-50 flex justify-end">
                {{ $footer }}
            </div>
        @endif
    </div>
</div>
