<?php

namespace App\Livewire\Checkpoints;

use App\Models\Checkpoint;
use App\Models\Folder;
use App\Services\GeocodingService;
use Livewire\Attributes\Layout;
use Livewire\Component;

/*
| Checkpoints library: a reusable set of named locations (grouped into
| folders) that trips can be planned around. Search, create/edit modal,
| and delete all live in this one component — checkpoints don't carry
| the nested per-item state Trips does, so no child components needed.
*/
#[Layout('components.layouts.app')]
class CheckpointManager extends Component
{
    public string $search = '';

    public bool $showModal = false;
    public ?int $checkpointId = null;
    public string $location = '';
    public string $description = '';
    public string $address = '';
    public ?float $latitude = null;
    public ?float $longitude = null;
    public ?int $folderId = null;
    public string $newFolderName = '';

    public function openCreate(): void
    {
        $this->resetValidation();
        $this->reset(['checkpointId', 'location', 'description', 'address', 'latitude', 'longitude', 'folderId', 'newFolderName']);
        $this->showModal = true;
    }

    public function openEdit(int $checkpointId): void
    {
        $checkpoint = Checkpoint::findOrFail($checkpointId);

        $this->resetValidation();
        $this->checkpointId = $checkpoint->id;
        $this->location = $checkpoint->location;
        $this->description = $checkpoint->description ?? '';
        $this->address = $checkpoint->address ?? '';
        $this->latitude = $checkpoint->latitude ? (float) $checkpoint->latitude : null;
        $this->longitude = $checkpoint->longitude ? (float) $checkpoint->longitude : null;
        $this->folderId = $checkpoint->folder_id;
        $this->newFolderName = '';
        $this->showModal = true;
    }

    public function close(): void
    {
        $this->showModal = false;
    }

    public function updated(string $name): void
    {
        if ($name === 'address') {
            $address = trim($this->address);

            if ($address === '') {
                $this->latitude = null;
                $this->longitude = null;
                return;
            }

            $result = GeocodingService::geocodeAddress($address);
            $this->latitude = $result['latitude'] ?? null;
            $this->longitude = $result['longitude'] ?? null;
        }
    }

    public function rules(): array
    {
        return [
            'location' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'address' => ['nullable', 'string', 'max:255'],
            'folderId' => ['nullable', 'exists:folders,id'],
            'newFolderName' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        $folderId = $validated['folderId'] ?: null;
        if (! empty($validated['newFolderName'])) {
            $folderId = Folder::create(['name' => $validated['newFolderName']])->id;
        }

        $attributes = [
            'location' => $validated['location'],
            'description' => $validated['description'] ?: null,
            'address' => $validated['address'] ?: null,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'coordinates' => ($this->latitude && $this->longitude) ? "{$this->latitude},{$this->longitude}" : null,
            'folder_id' => $folderId,
        ];

        if ($this->checkpointId) {
            Checkpoint::findOrFail($this->checkpointId)->update($attributes);
        } else {
            $attributes['user_id'] = auth()->id();
            Checkpoint::create($attributes);
        }

        $this->showModal = false;
        session()->flash('message', $this->checkpointId ? 'Checkpoint updated!' : 'Checkpoint created!');
    }

    public function delete(int $checkpointId): void
    {
        Checkpoint::findOrFail($checkpointId)->delete();
        session()->flash('message', 'Checkpoint deleted!');
    }

    public function getCheckpoints()
    {
        return Checkpoint::with(['user', 'trips', 'folder'])
            ->when($this->search !== '', function ($query) {
                $term = '%' . $this->search . '%';
                $query->where(fn ($q) => $q->where('location', 'like', $term)
                    ->orWhere('description', 'like', $term)
                    ->orWhereHas('folder', fn ($f) => $f->where('name', 'like', $term)));
            })
            ->get();
    }

    public function render()
    {
        $checkpoints = $this->getCheckpoints();

        return view('livewire.checkpoints.manager', [
            'folders' => Folder::orderBy('name')->get(),
            'checkpointsByFolder' => $checkpoints->whereNotNull('folder_id')->groupBy('folder_id'),
            'unassignedCheckpoints' => $checkpoints->whereNull('folder_id'),
        ]);
    }
}
