<div
    x-show="showRemoveModal"
    x-transition:enter="ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center"
    style="display: none;"
>
    <div class="absolute inset-0 bg-black/50" @click="showRemoveModal = false"></div>

    <div
        x-show="showRemoveModal"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="relative bg-white dark:bg-neutral-800 rounded-xl shadow-xl border border-gray-200 dark:border-neutral-700 p-6 w-full max-w-sm mx-4"
    >
        <div class="flex items-start gap-4 mb-5">
            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                </svg>
            </div>
            <div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white" x-text="removeModalTitle"></h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Are you sure you want to remove
                    <span class="font-semibold text-gray-700 dark:text-gray-200" x-text="'&apos;' + removeItemName + '&apos;'"></span>
                    from your <span x-text="removeContextLabel"></span>?
                </p>
            </div>
        </div>
        <div class="flex justify-end gap-3">
            <button
                type="button"
                @click="showRemoveModal = false"
                class="px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 bg-white dark:bg-neutral-700 border border-gray-300 dark:border-neutral-600 rounded-lg hover:bg-gray-50 dark:hover:bg-neutral-600 transition-colors"
            >Cancel</button>
            <button
                type="button"
                @click="confirm()"
                class="px-4 py-2 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors"
            >Yes, Remove</button>
        </div>
    </div>
</div>
