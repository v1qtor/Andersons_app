@php
    $tripCount = $checkpoint->trips->count();
@endphp

<div wire:key="checkpoint-{{ $checkpoint->id }}" class="bg-gray-50 dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 rounded-xl p-5 flex flex-col justify-between hover:shadow-md transition-shadow">
    <div>
        <div class="flex items-center gap-2 mb-2 flex-wrap">
            <span class="text-lg font-bold text-gray-900 dark:text-white">{{ $checkpoint->location }}</span>
            @if ($tripCount > 0)
                <span class="bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300 text-xs px-2 py-1 rounded-full font-medium">{{ $tripCount }} {{ Str::plural('trip', $tripCount) }}</span>
            @endif
        </div>
        @if ($checkpoint->address)
            <p class="text-sm text-gray-700 dark:text-gray-300 mb-1">{{ $checkpoint->address }}</p>
        @endif
        @if ($checkpoint->description)
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">{{ $checkpoint->description }}</p>
        @endif
        <p class="text-xs text-gray-500 dark:text-gray-400">By {{ $checkpoint->user?->name ?? 'System' }}</p>
    </div>
    @if ($canManage)
        <div class="flex justify-between items-center gap-2 mt-4 pt-3 border-t border-gray-200 dark:border-neutral-700">
            <button wire:click="openEdit({{ $checkpoint->id }})" class="flex-1 bg-indigo-100 dark:bg-indigo-900/30 hover:bg-indigo-200 dark:hover:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 text-sm font-semibold py-1.5 px-4 rounded-lg">
                Edit
            </button>
            <button
                wire:click="confirmDelete({{ $checkpoint->id }})"
                class="bg-red-100 dark:bg-red-900/20 hover:bg-red-200 dark:hover:bg-red-900/40 text-red-700 dark:text-red-400 text-sm font-semibold py-1.5 px-4 rounded-lg"
            >
                Delete
            </button>
        </div>
    @endif
</div>
