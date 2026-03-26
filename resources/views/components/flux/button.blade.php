@php($type = $type ?? 'secondary')
@php($type = $type ?? 'secondary')
<button {{ $attributes->merge(['class' => 'px-4 py-2 rounded font-semibold transition ' . ($type === 'primary' ? 'bg-blue-600 text-white hover:bg-blue-700' : 'bg-gray-200 text-gray-800 hover:bg-gray-300')]) }}>
    @if(isset($icon))<span class="mr-2">{!! $icon !!}</span>@endif
    {{ $slot }}
</button>