<?php

namespace App\Livewire\Trips;

use App\Models\AttachedFile;
use App\Models\Checkpoint;
use App\Models\CheckpointImage;
use App\Models\Status;
use App\Models\Trip;
use App\Services\GeocodingService;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Reactive;
use Livewire\Component;
use Livewire\WithFileUploads;

/*
| One trip's detail card: status/overdue banner, participants,
| checkpoint route (add/remove/reorder/arrive, per-checkpoint photos),
| documents, and the route map. Isolated from TripManager's list so
| in-card actions don't re-render every other trip.
|
| Takes a trip ID rather than the Trip model itself: Livewire's
| reactive-prop protection throws if a child lazy-loads a relation on
| a #[Reactive] Eloquent model that the parent didn't already eager
| load, so each method loads its own fresh copy instead.
*/
class TripCard extends Component
{
    use WithFileUploads;

    #[Reactive]
    public int $tripId;

    public bool $confirmingCancel = false;
    public bool $confirmingDelete = false;

    public bool $showAddCheckpoint = false;
    public ?int $newCheckpointId = null;
    public string $newTempName = '';
    public string $newTempAddress = '';
    public ?float $newTempLat = null;
    public ?float $newTempLng = null;

    public ?int $uploadingImagesForCheckpointId = null;
    public array $newImages = [];

    public $newDocument = null;

    public ?string $viewingImageUrl = null;

    public function isManager(): bool
    {
        return auth()->user()->canManageTrips();
    }

    private function loadTrip(): Trip
    {
        return Trip::with(['status', 'tripCategory', 'users', 'checkpoints', 'checkpointImages', 'attachedFiles', 'plusOnes'])
            ->findOrFail($this->tripId);
    }

    public function confirmCancel(): void
    {
        abort_unless($this->isManager(), 403);
        $this->confirmingCancel = true;
    }

    public function closeCancelConfirm(): void
    {
        $this->confirmingCancel = false;
    }

    public function cancel(): void
    {
        abort_unless($this->isManager(), 403);

        $cancelled = Status::where('name', 'cancelled')->where('type', 'trip')->first();
        if ($cancelled) {
            $this->loadTrip()->update(['status_id' => $cancelled->id]);
        }

        $this->confirmingCancel = false;
        session()->flash('message', 'Trip cancelled.');
        $this->dispatch('trip-changed');
    }

    public function confirmDelete(): void
    {
        abort_unless($this->isManager(), 403);
        $this->confirmingDelete = true;
    }

    public function closeDeleteConfirm(): void
    {
        $this->confirmingDelete = false;
    }

    public function delete(): void
    {
        abort_unless($this->isManager(), 403);

        $trip = $this->loadTrip();
        foreach ($trip->checkpoints as $checkpoint) {
            if ($checkpoint->pivot->is_temporary) {
                $trip->checkpoints()->detach($checkpoint->id);
                $checkpoint->delete();
            }
        }
        $trip->delete();

        session()->flash('message', 'Trip deleted!');
        $this->dispatch('trip-changed');
    }

    public function openAddCheckpoint(): void
    {
        abort_unless($this->isManager(), 403);
        $this->reset(['newCheckpointId', 'newTempName', 'newTempAddress', 'newTempLat', 'newTempLng']);
        $this->showAddCheckpoint = true;
    }

    public function closeAddCheckpoint(): void
    {
        $this->showAddCheckpoint = false;
    }

    public function updated(string $name): void
    {
        if ($name === 'newTempAddress') {
            $address = trim($this->newTempAddress);

            if ($address === '') {
                $this->newTempLat = null;
                $this->newTempLng = null;
                return;
            }

            $result = GeocodingService::geocodeAddress($address);
            $this->newTempLat = $result['latitude'] ?? null;
            $this->newTempLng = $result['longitude'] ?? null;
        }
    }

    public function addCheckpoint(): void
    {
        abort_unless($this->isManager(), 403);

        $trip = $this->loadTrip();
        $max = (int) $trip->checkpoints()->max('order');

        if ($this->newCheckpointId) {
            $trip->checkpoints()->attach($this->newCheckpointId, [
                'order' => $max + 1,
                'is_temporary' => false,
            ]);
        } elseif (trim($this->newTempName) !== '') {
            $checkpoint = Checkpoint::create([
                'location' => $this->newTempName,
                'address' => $this->newTempAddress ?: null,
                'latitude' => $this->newTempLat,
                'longitude' => $this->newTempLng,
                'coordinates' => ($this->newTempLat && $this->newTempLng) ? "{$this->newTempLat},{$this->newTempLng}" : null,
            ]);

            $trip->checkpoints()->attach($checkpoint->id, [
                'order' => $max + 1,
                'is_temporary' => true,
                'temp_location' => $this->newTempName,
                'temp_address' => $this->newTempAddress ?: null,
            ]);
        }

        $this->showAddCheckpoint = false;
        session()->flash('message', 'Checkpoint added.');
        $this->dispatch('trip-changed');
    }

    public function removeCheckpoint(int $checkpointId): void
    {
        abort_unless($this->isManager(), 403);

        $trip = $this->loadTrip();
        $checkpoint = $trip->checkpoints()->find($checkpointId);
        if (! $checkpoint) {
            return;
        }

        $isTemporary = $checkpoint->pivot->is_temporary;
        $trip->checkpoints()->detach($checkpointId);
        if ($isTemporary) {
            $checkpoint->delete();
        }

        $this->reorderSequentially($trip);
        $this->dispatch('trip-changed');
    }

    public function reorderUp(int $checkpointId): void
    {
        $this->swapOrder($checkpointId, -1);
    }

    public function reorderDown(int $checkpointId): void
    {
        $this->swapOrder($checkpointId, 1);
    }

    private function swapOrder(int $checkpointId, int $direction): void
    {
        abort_unless($this->isManager(), 403);

        $trip = $this->loadTrip();
        $ordered = $trip->checkpoints()->orderByPivot('order')->get();
        $index = $ordered->search(fn ($c) => $c->id === $checkpointId);
        $swapWith = $index + $direction;

        if ($index === false || ! $ordered->has($swapWith)) {
            return;
        }

        $a = $ordered[$index];
        $b = $ordered[$swapWith];

        $trip->checkpoints()->updateExistingPivot($a->id, ['order' => $b->pivot->order]);
        $trip->checkpoints()->updateExistingPivot($b->id, ['order' => $a->pivot->order]);

        $this->dispatch('trip-changed');
    }

    private function reorderSequentially(Trip $trip): void
    {
        $ordered = $trip->checkpoints()->orderByPivot('order')->get();
        foreach ($ordered as $i => $checkpoint) {
            $trip->checkpoints()->updateExistingPivot($checkpoint->id, ['order' => $i + 1]);
        }
    }

    public function markArrived(int $checkpointId): void
    {
        $this->loadTrip()->checkpoints()->updateExistingPivot($checkpointId, [
            'is_confirmed' => true,
            'arrival_date' => now(),
        ]);
    }

    public function unmarkArrived(int $checkpointId): void
    {
        $this->loadTrip()->checkpoints()->updateExistingPivot($checkpointId, [
            'is_confirmed' => false,
            'arrival_date' => null,
        ]);
    }

    public function openImageUpload(int $checkpointId): void
    {
        $this->uploadingImagesForCheckpointId = $checkpointId;
        $this->newImages = [];
    }

    public function uploadImages(): void
    {
        $this->validate(['newImages.*' => 'image|max:5120']);

        foreach ($this->newImages as $image) {
            $path = $image->store('checkpoint-images', 'public');
            CheckpointImage::create([
                'trip_id' => $this->tripId,
                'checkpoint_id' => $this->uploadingImagesForCheckpointId,
                'image_path' => $path,
                'uploaded_by' => auth()->id(),
            ]);
        }

        $this->newImages = [];
        $this->uploadingImagesForCheckpointId = null;
        session()->flash('message', 'Image(s) uploaded successfully.');
    }

    public function removeImage(int $imageId): void
    {
        $image = CheckpointImage::findOrFail($imageId);
        if ($image->image_path && Storage::disk('public')->exists($image->image_path)) {
            Storage::disk('public')->delete($image->image_path);
        }
        $image->delete();
    }

    public function viewImage(string $url): void
    {
        $this->viewingImageUrl = $url;
    }

    public function closeImageViewer(): void
    {
        $this->viewingImageUrl = null;
    }

    public function uploadDocument(): void
    {
        abort_unless($this->isManager(), 403);

        $this->validate(['newDocument' => 'required|file|max:10240']);

        $path = $this->newDocument->store('trip-files', 'public');
        AttachedFile::create([
            'name' => $this->newDocument->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $this->newDocument->getClientMimeType(),
            'trip_id' => $this->tripId,
        ]);

        $this->newDocument = null;
        session()->flash('message', 'File uploaded.');
    }

    public function removeDocument(int $fileId): void
    {
        abort_unless($this->isManager(), 403);

        $file = AttachedFile::findOrFail($fileId);
        if (Storage::disk('public')->exists($file->file_path)) {
            Storage::disk('public')->delete($file->file_path);
        }
        $file->delete();
    }

    public function render()
    {
        return view('livewire.trips.card', [
            'trip' => $this->loadTrip(),
            'isManager' => $this->isManager(),
            'availableCheckpoints' => Checkpoint::whereNotNull('folder_id')
                ->whereDoesntHave('trips', fn ($q) => $q->where('trips.id', $this->tripId))
                ->orderBy('location')
                ->get(),
        ]);
    }
}
