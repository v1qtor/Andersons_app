@props([
    'show' => false,
    'title' => 'Confirm Action',
    'message' => '',
    'cancelAction' => 'closeModal',
    'confirmAction' => 'confirm',
    'confirmLabel' => 'Confirm',
    'confirmTone' => 'primary',
])

@if($show)
    <div class="fixed inset-0 bg-black/50 dark:bg-black/70 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-neutral-800 rounded-xl max-w-md w-full">
            <div class="p-6 border-b border-gray-200 dark:border-neutral-700">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $title }}</h2>
            </div>

            <div class="p-6">
                <p class="text-gray-700 dark:text-gray-300">{{ $message }}</p>
            </div>

            <div class="flex gap-3 p-6 border-t border-gray-200 dark:border-neutral-700">
                <button wire:click="{{ $cancelAction }}" class="flex-1 px-4 py-2 border border-gray-300 dark:border-neutral-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-neutral-700 transition-colors font-medium">
                    Cancel
                </button>
                <button
                    wire:click="{{ $confirmAction }}"
                    @class([
                        'flex-1 px-4 py-2 text-white rounded-lg transition-colors font-medium',
                        'bg-indigo-600 hover:bg-indigo-700' => $confirmTone === 'primary',
                        'bg-green-700 hover:bg-green-800' => $confirmTone === 'success',
                        'bg-red-600 hover:bg-red-700' => $confirmTone === 'danger',
                    ])
                >
                    {{ $confirmLabel }}
                </button>
            </div>
        </div>
    </div>
@endif
