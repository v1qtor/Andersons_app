<div
    class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50"
    wire:click.self="$set('showDeleteModal', false)"
    @keydown.escape.window="$wire.set('showDeleteModal', false)"
>
    <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-2xl max-w-sm w-full p-6">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/30 mb-4">
                <flux:icon name="exclamation-triangle" class="size-6 text-red-600 dark:text-red-400" />
            </div>
            <h3 class="text-lg font-bold text-neutral-900 dark:text-neutral-100 mb-2">
                {{ __('Delete Task') }}
            </h3>
            <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-6">
                {{ __('Are you sure you want to delete this task? This action cannot be undone.') }}
            </p>
            <div class="flex justify-center gap-3">
                <flux:button variant="ghost" wire:click="$set('showDeleteModal', false)">
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button variant="danger" wire:click="deleteTask">
                    {{ __('Delete') }}
                </flux:button>
            </div>
        </div>
    </div>
</div>

