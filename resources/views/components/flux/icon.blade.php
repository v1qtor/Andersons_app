@props(['name'])

@switch($name)
    @case('edit')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-blue-600"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487a2.1 2.1 0 1 1 2.97 2.97L7.5 19.788l-4 1 1-4 13.362-13.3Z" /></svg>
        @break
    @case('delete')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-red-600"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
        @break
    @case('deactivate')
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-yellow-600">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5" fill="#FEF08A" />
            <path stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" d="M8 12h8m-4-4v8" />
        </svg>
        @break
    @default
        <span class="text-gray-400">?</span>
@endswitch
