@props([
    'show' => false,
    'title' => '',
    'zIndex' => 'z-50',
    'maxWidth' => 'max-w-2xl',
    'closeAction' => null,
    'escapeAction' => null,
    'scrollableBody' => true,
    'lockBodyScroll' => false,
    'bodyPadding' => 'p-6',
    'titleClass' => 'text-2xl font-bold text-gray-900 dark:text-white',
    'overlayClass' => '',
    'panelClass' => '',
    'headerClass' => '',
])

@php
    $derivedEscapeAction = $escapeAction;

    if (! $derivedEscapeAction && $closeAction && ! str_contains($closeAction, '(') && ! str_contains($closeAction, '$')) {
        $derivedEscapeAction = '$wire.' . $closeAction . '()';
    }
@endphp

@if ($show)
    <div
        class="fixed inset-0 {{ $zIndex }} flex items-center justify-center bg-black/50 p-4 dark:bg-black/70 {{ $overlayClass }}"
        @if($lockBodyScroll)
            x-data="{ init() { document.body.style.overflow = 'hidden' }, destroy() { document.body.style.overflow = '' } }"
        @endif
        @if($closeAction)
            wire:click.self="{{ $closeAction }}"
        @endif
        @if($derivedEscapeAction)
            @keydown.escape.window="{{ $derivedEscapeAction }}"
        @endif
    >
        <div class="w-full {{ $maxWidth }} rounded-xl bg-white dark:bg-neutral-800 {{ $scrollableBody ? 'max-h-96 overflow-y-auto' : '' }} {{ $panelClass }}">
            <div class="sticky top-0 flex items-center justify-between border-b border-gray-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-800 {{ $headerClass }}">
                <h2 class="{{ $titleClass }}">{{ $title }}</h2>

                <div class="flex items-center gap-2">
                    {{ $headerActions ?? '' }}

                    @if ($closeAction)
                        <button wire:click="{{ $closeAction }}" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300" aria-label="Close modal">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    @endif
                </div>
            </div>

            <div class="{{ $bodyPadding }}">
                {{ $slot }}
            </div>
        </div>
    </div>
@endif
