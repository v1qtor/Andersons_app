@props([
    'editingTaskId' => null,
    'taskCategories' => collect(),
    'taskPriorities' => collect(),
])

<div
    class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50"
    wire:click.self="$set('showTaskModal', false)"
    x-data="{
        init() { document.body.style.overflow = 'hidden' },
        destroy() { document.body.style.overflow = '' }
    }"
    @keydown.escape.window="$wire.set('showTaskModal', false)"
>
    <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white dark:bg-zinc-800 border-b border-neutral-200 dark:border-neutral-700 p-6 flex items-center justify-between z-10">
            <h3 class="text-xl font-bold text-neutral-900 dark:text-neutral-100">
                {{ $editingTaskId ? __('Edit Task') : __('New Task') }}
            </h3>
            <flux:button variant="ghost" size="sm" wire:click="$set('showTaskModal', false)" icon="x-mark" />
        </div>

        <form wire:submit="saveTask" class="p-6 space-y-4">
            <div>
                <flux:input wire:model="title" :label="__('Title')" placeholder="{{ __('Task title') }}" required />
                @error('title') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div>
                <flux:textarea wire:model="description" :label="__('Description')" placeholder="{{ __('Optional description') }}" rows="3" />
                @error('description') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <flux:input wire:model="startDate" :label="__('Start Date & Time')" type="datetime-local" required />
                    @error('startDate') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <flux:input wire:model="endDate" :label="__('End Date & Time')" type="datetime-local" />
                    @error('endDate') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <flux:select wire:model="taskCategoryId" :label="__('Category')" placeholder="{{ __('Select category') }}">
                        @foreach ($taskCategories as $cat)
                            <flux:select.option value="{{ $cat->id }}">{{ $cat->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    @error('taskCategoryId') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <flux:select wire:model="taskPriorityId" :label="__('Priority')" placeholder="{{ __('Select priority') }}">
                        @foreach ($taskPriorities as $pri)
                            <flux:select.option value="{{ $pri->id }}">{{ $pri->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    @error('taskPriorityId') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-neutral-200 dark:border-neutral-700">
                <flux:button variant="ghost" wire:click="$set('showTaskModal', false)">
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button type="submit" variant="primary">
                    {{ $editingTaskId ? __('Update Task') : __('Create Task') }}
                </flux:button>
            </div>
        </form>
    </div>
</div>

