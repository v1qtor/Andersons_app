@php
    $statusName = $trip->status->name ?? 'upcoming';
    $toneClasses = match (true) {
        $trip->is_overdue => 'bg-red-600 text-white',
        $statusName === 'active' => 'bg-green-600 text-white',
        $statusName === 'upcoming' => 'bg-blue-600 text-white',
        $statusName === 'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
        default => 'bg-gray-500 text-white',
    };
    $duration = (int) $trip->start_date->diffInDays($trip->end_date);
    $orderedCheckpoints = $trip->checkpoints;
    $totalCheckpoints = $orderedCheckpoints->count();
    $confirmedCheckpoints = $orderedCheckpoints->where('pivot.is_confirmed', true)->count();
    $progressPct = $totalCheckpoints > 0 ? ($confirmedCheckpoints / $totalCheckpoints) * 100 : 0;
    $geoCheckpoints = $orderedCheckpoints
        ->filter(fn ($c) => $c->latitude && $c->longitude)
        ->map(fn ($c) => ['location' => $c->location, 'lat' => (float) $c->latitude, 'lng' => (float) $c->longitude])
        ->values();
@endphp

<x-ui.section-card class="!p-0 overflow-hidden {{ $trip->is_overdue ? 'border-red-300 dark:border-red-700' : '' }}" wire:key="trip-card-{{ $trip->id }}">
    <x-confirm-action-modal
        :show="$confirmingCancel"
        title="Cancel Trip"
        message="Are you sure you want to cancel this trip?"
        cancelAction="closeCancelConfirm"
        confirmAction="cancel"
        confirmLabel="Cancel Trip"
        confirmTone="danger"
    />
    <x-confirm-action-modal
        :show="$confirmingDelete"
        title="Delete Trip"
        message="Are you sure you want to delete this trip? This cannot be undone."
        cancelAction="closeDeleteConfirm"
        confirmAction="delete"
        confirmLabel="Delete"
        confirmTone="danger"
    />
    <x-confirm-action-modal
        :show="$confirmingRemoveCheckpoint !== null"
        title="Remove Checkpoint"
        message='Remove "{{ $confirmingRemoveCheckpoint?->location }}" from this trip?'
        cancelAction="closeRemoveCheckpointConfirm"
        confirmAction="removeCheckpoint"
        confirmLabel="Remove"
        confirmTone="danger"
    />
    <x-confirm-action-modal
        :show="$confirmingRemoveImageId !== null"
        title="Delete Image"
        message="Delete this image? This cannot be undone."
        cancelAction="closeRemoveImageConfirm"
        confirmAction="removeImage"
        confirmLabel="Delete"
        confirmTone="danger"
    />
    <x-confirm-action-modal
        :show="$confirmingRemoveDocument !== null"
        title="Remove Document"
        message='Remove "{{ $confirmingRemoveDocument?->name }}" from this trip?'
        cancelAction="closeRemoveDocumentConfirm"
        confirmAction="removeDocument"
        confirmLabel="Remove"
        confirmTone="danger"
    />

    <x-ui.detail-modal :show="$viewingImageUrl !== null" title="Checkpoint Photo" closeAction="closeImageViewer">
        @if ($viewingImageUrl)
            <img src="{{ $viewingImageUrl }}" class="max-w-full h-auto max-h-[70vh] mx-auto rounded-lg" />
        @endif
    </x-ui.detail-modal>

    <x-ui.detail-modal :show="$showAddCheckpoint" title="Add Checkpoint" closeAction="closeAddCheckpoint">
        <div class="space-y-4">
            @if ($availableCheckpoints->isNotEmpty())
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Permanent Checkpoint</label>
                    <select wire:model="newCheckpointId" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white">
                        <option value="">Choose…</option>
                        @foreach ($availableCheckpoints as $cp)
                            <option value="{{ $cp->id }}">{{ $cp->location }}</option>
                        @endforeach
                    </select>
                </div>
                <p class="text-center text-sm text-gray-500 dark:text-gray-400 font-semibold">— OR —</p>
            @endif
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Temporary Checkpoint (only this trip)</label>
                <input type="text" wire:model="newTempName" placeholder="Name" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white mb-2" />
                <input type="text" wire:model.live.debounce.600ms="newTempAddress" placeholder="Address" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white mb-2" />
                <x-trips.address-map :address="$newTempAddress" height="160px" />
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" wire:click="closeAddCheckpoint" class="px-5 py-2.5 rounded-lg border border-gray-300 dark:border-neutral-600 text-gray-700 dark:text-gray-300 font-semibold hover:bg-gray-50 dark:hover:bg-neutral-700">Cancel</button>
                <x-flux.button wire:click="addCheckpoint" variant="primary">Add</x-flux.button>
            </div>
        </div>
    </x-ui.detail-modal>

    <div class="p-6">
        <div class="flex flex-wrap justify-between items-start gap-3 mb-3">
            <div>
                <div class="flex items-center gap-3 flex-wrap">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $trip->name }}</h3>
                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide {{ $toneClasses }}">
                        {{ $trip->is_overdue ? 'Overdue' : $statusName }}
                    </span>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    {{ $trip->start_date->format('d M Y') }} → {{ $trip->end_date->format('d M Y') }}
                    <span class="text-gray-400">· {{ $duration }} {{ Str::plural('day', $duration) }}</span>
                </p>
                @if ($trip->buffer_alert)
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Expected return: {{ $trip->buffer_alert->format('d M Y H:i') }}</p>
                @endif
            </div>

            @if ($isManager)
                <x-ui.row-actions>
                    <x-flux.button variant="ghost" size="sm" wire:click="$dispatch('open-trip-form', { tripId: {{ $trip->id }} })" icon="pencil" />
                    @if ($statusName !== 'cancelled')
                        <x-flux.button variant="ghost" size="sm" wire:click="confirmCancel" icon="x-circle" />
                    @endif
                    <x-flux.button variant="ghost" size="sm" wire:click="confirmDelete" icon="trash" class="!text-red-600 hover:!text-red-700 dark:!text-red-400" />
                </x-ui.row-actions>
            @endif
        </div>

        @if ($trip->description)
            <p class="text-sm text-gray-700 dark:text-gray-300 mb-3">{{ $trip->description }}</p>
        @endif

        @if ($trip->is_overdue)
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-300 dark:border-red-800 text-red-800 dark:text-red-300 rounded-lg px-4 py-3 mb-4 text-sm">
                <strong>⚠ Overdue — expected return has passed.</strong> Please check that everyone is accounted for.
            </div>
        @endif

        <div class="mb-4">
            <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Route Progress: <span class="font-semibold text-gray-900 dark:text-white">{{ $confirmedCheckpoints }} of {{ $totalCheckpoints }} reached</span></div>
            <div class="w-full bg-gray-200 dark:bg-neutral-700 rounded-full h-2">
                <div class="bg-indigo-500 h-2 rounded-full" style="width: {{ $progressPct }}%"></div>
            </div>
        </div>

        <div class="mb-4">
            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Participants ({{ $trip->users->count() + $trip->plusOnes->count() }})</p>
            <div class="flex flex-wrap gap-2">
                @foreach ($trip->users as $user)
                    <span class="bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300 px-3 py-1 rounded-full text-xs font-medium">{{ $user->name }}</span>
                @endforeach
                @foreach ($trip->plusOnes as $plusOne)
                    <span class="bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300 px-3 py-1 rounded-full text-xs font-medium" title="Guest">👤 {{ $plusOne->name }}</span>
                @endforeach
            </div>
        </div>

        @if ($trip->notes)
            <div class="mb-4">
                <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Notes</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $trip->notes }}</p>
            </div>
        @endif

        <div class="mb-4">
            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Route Checkpoints</p>
            <div class="space-y-2">
                @foreach ($orderedCheckpoints as $index => $checkpoint)
                    <div wire:key="checkpoint-{{ $checkpoint->id }}" class="border rounded-lg p-3 {{ $checkpoint->pivot->is_confirmed ? 'bg-green-50 border-green-200 dark:bg-green-900/10 dark:border-green-800' : 'bg-gray-50 border-gray-200 dark:bg-neutral-900 dark:border-neutral-700' }}">
                        <div class="flex justify-between items-start gap-2">
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-sm text-gray-900 dark:text-white">{{ $index + 1 }}. {{ $checkpoint->location }}</p>
                                @if ($checkpoint->address)<p class="text-xs text-gray-500 dark:text-gray-400">{{ $checkpoint->address }}</p>@endif
                            </div>
                            @if ($isManager)
                                <div class="flex gap-1 shrink-0">
                                    <button wire:click="reorderUp({{ $checkpoint->id }})" class="w-7 h-7 rounded bg-gray-200 dark:bg-neutral-700 text-gray-700 dark:text-gray-300 text-xs font-bold hover:bg-gray-300 dark:hover:bg-neutral-600">↑</button>
                                    <button wire:click="reorderDown({{ $checkpoint->id }})" class="w-7 h-7 rounded bg-gray-200 dark:bg-neutral-700 text-gray-700 dark:text-gray-300 text-xs font-bold hover:bg-gray-300 dark:hover:bg-neutral-600">↓</button>
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-wrap gap-2 items-center mt-2">
                            @if (! $checkpoint->pivot->is_confirmed)
                                <button wire:click="markArrived({{ $checkpoint->id }})" class="bg-green-600 hover:bg-green-700 text-white text-xs font-semibold py-1.5 px-3 rounded-lg">Mark Arrived</button>
                            @else
                                <button wire:click="unmarkArrived({{ $checkpoint->id }})" class="bg-gray-200 dark:bg-neutral-700 hover:bg-gray-300 dark:hover:bg-neutral-600 text-gray-800 dark:text-gray-200 text-xs font-semibold py-1.5 px-3 rounded-lg">↶ Undo</button>
                            @endif
                            <button wire:click="openImageUpload({{ $checkpoint->id }})" class="bg-indigo-100 dark:bg-indigo-900/30 hover:bg-indigo-200 dark:hover:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 text-xs font-semibold py-1.5 px-3 rounded-lg">+ Photo</button>
                            @if ($isManager)
                                <button wire:click="confirmRemoveCheckpoint({{ $checkpoint->id }})" class="bg-red-100 dark:bg-red-900/20 hover:bg-red-200 dark:hover:bg-red-900/40 text-red-700 dark:text-red-400 text-xs font-semibold py-1.5 px-3 rounded-lg">Remove</button>
                            @endif
                        </div>

                        @if ($uploadingImagesForCheckpointId === $checkpoint->id)
                            <div class="mt-3 flex items-center gap-2">
                                <input type="file" wire:model="newImages" multiple accept="image/*" class="text-xs flex-1" />
                                <x-flux.button size="sm" wire:click="uploadImages" variant="primary">Upload</x-flux.button>
                            </div>
                            @error('newImages.*') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        @endif

                        @php $cpImages = $trip->checkpointImages->where('checkpoint_id', $checkpoint->id); @endphp
                        @if ($cpImages->isNotEmpty())
                            <div class="grid grid-cols-3 sm:grid-cols-4 gap-2 mt-3">
                                @foreach ($cpImages as $image)
                                    <div class="relative group">
                                        <img src="{{ Storage::url($image->image_path) }}" wire:click="viewImage('{{ Storage::url($image->image_path) }}')" class="w-full h-20 object-cover rounded cursor-pointer" />
                                        @if ($isManager)
                                            <button wire:click="confirmRemoveImage({{ $image->id }})" class="absolute top-1 right-1 hidden group-hover:flex w-5 h-5 items-center justify-center rounded-full bg-red-600 text-white text-xs">✕</button>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach

                @if ($isManager)
                    <button wire:click="openAddCheckpoint" class="w-full bg-indigo-50 dark:bg-indigo-900/20 hover:bg-indigo-100 dark:hover:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 text-sm font-semibold py-2 rounded-lg">+ Add Checkpoint</button>
                @endif
            </div>
        </div>

        <div class="mb-4">
            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Documents & Permits</p>
            <div class="space-y-2 mb-2">
                @foreach ($trip->attachedFiles as $file)
                    <div class="flex justify-between items-center bg-gray-50 dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 rounded-lg px-3 py-2">
                        <div class="text-sm text-gray-800 dark:text-gray-200">
                            {{ $file->name }}
                            <a href="{{ Storage::url($file->file_path) }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 ml-2 text-xs underline">Download</a>
                        </div>
                        @if ($isManager)
                            <button wire:click="confirmRemoveDocument({{ $file->id }})" class="text-red-500 hover:text-red-700 text-sm font-bold">✕</button>
                        @endif
                    </div>
                @endforeach
            </div>
            @if ($isManager)
                <div class="flex gap-2">
                    <input type="file" wire:model="newDocument" class="flex-1 text-sm px-3 py-2 border border-gray-300 dark:border-neutral-600 rounded-lg dark:bg-neutral-700 dark:text-white" />
                    <x-flux.button size="sm" wire:click="uploadDocument" variant="primary">Upload</x-flux.button>
                </div>
                @error('newDocument') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            @endif
        </div>

        <div>
            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Route Map</p>
            <x-trips.route-map :checkpoints="$geoCheckpoints" />
        </div>
    </div>
</x-ui.section-card>
