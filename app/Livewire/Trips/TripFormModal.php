<?php

namespace App\Livewire\Trips;

use App\Models\Checkpoint;
use App\Models\PlusOne;
use App\Models\Trip;
use App\Models\TripCategory;
use App\Models\User;
use App\Services\GeocodingService;
use Livewire\Attributes\On;
use Livewire\Component;

/*
| Shared create/edit modal for planning a trip: core trip details,
| participants, permanent checkpoint picks, ad-hoc temporary
| checkpoints (geocoded live as they're typed) and plus-one guests.
*/
class TripFormModal extends Component
{
    public bool $showModal = false;

    public ?int $tripId = null;

    public string $name = '';

    public string $description = '';

    public string $notes = '';

    public string $start_date = '';

    public string $end_date = '';

    public ?int $trip_category_id = null;

    public string $buffer_alert = '';

    public array $userIds = [];

    public array $checkpointIds = [];

    public array $tempCheckpoints = [];

    public array $plusOnes = [];

    #[On('open-trip-form')]
    public function open(?int $tripId = null): void
    {
        $this->resetValidation();
        $this->tripId = $tripId;
        $this->tempCheckpoints = [];
        $this->plusOnes = [];

        if ($tripId) {
            $trip = Trip::with(['users', 'checkpoints' => fn ($q) => $q->wherePivot('is_temporary', false)])->findOrFail($tripId);
            $this->authorize('update', $trip);

            $this->name = $trip->name;
            $this->description = $trip->description ?? '';
            $this->notes = $trip->notes ?? '';
            $this->start_date = $trip->start_date->format('Y-m-d\TH:i');
            $this->end_date = $trip->end_date->format('Y-m-d\TH:i');
            $this->trip_category_id = $trip->trip_category_id;
            $this->buffer_alert = $trip->buffer_alert ? $trip->buffer_alert->format('Y-m-d\TH:i') : '';
            $this->userIds = $trip->users->pluck('id')->map(fn ($id) => (string) $id)->toArray();
            $this->checkpointIds = $trip->checkpoints->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        } else {
            $this->authorize('create', Trip::class);
            $this->reset(['name', 'description', 'notes', 'start_date', 'end_date', 'trip_category_id', 'buffer_alert', 'userIds', 'checkpointIds']);
        }

        $this->showModal = true;
    }

    public function close(): void
    {
        $this->showModal = false;
    }

    public function addTempCheckpoint(): void
    {
        $this->tempCheckpoints[] = ['name' => '', 'address' => '', 'latitude' => null, 'longitude' => null];
    }

    public function removeTempCheckpoint(int $index): void
    {
        unset($this->tempCheckpoints[$index]);
        $this->tempCheckpoints = array_values($this->tempCheckpoints);
    }

    public function addPlusOne(): void
    {
        $this->plusOnes[] = ['name' => '', 'email' => '', 'phone' => ''];
    }

    public function removePlusOne(int $index): void
    {
        unset($this->plusOnes[$index]);
        $this->plusOnes = array_values($this->plusOnes);
    }

    // Geocodes a temporary checkpoint's address in the background (for
    // internal storage/ordering) as soon as it's typed. The Google Maps
    // preview under the field is driven by the raw address text directly.
    public function updated(string $name): void
    {
        if (preg_match('/^tempCheckpoints\.(\d+)\.address$/', $name, $m)) {
            $index = (int) $m[1];
            $address = trim($this->tempCheckpoints[$index]['address'] ?? '');

            if ($address === '') {
                $this->tempCheckpoints[$index]['latitude'] = null;
                $this->tempCheckpoints[$index]['longitude'] = null;

                return;
            }

            $result = GeocodingService::geocodeAddress($address);
            $this->tempCheckpoints[$index]['latitude'] = $result['latitude'] ?? null;
            $this->tempCheckpoints[$index]['longitude'] = $result['longitude'] ?? null;
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'trip_category_id' => ['required', 'exists:trip_categories,id'],
            'buffer_alert' => ['nullable', 'date'],
            'userIds' => ['required', 'array', 'min:1'],
            'userIds.*' => ['exists:users,id'],
            'checkpointIds' => ['array'],
            'checkpointIds.*' => ['exists:checkpoints,id'],
            'tempCheckpoints.*.name' => ['nullable', 'string', 'max:255'],
            'tempCheckpoints.*.address' => ['nullable', 'string', 'max:255'],
            'plusOnes.*.name' => ['nullable', 'string', 'max:255'],
            'plusOnes.*.email' => ['nullable', 'email'],
            'plusOnes.*.phone' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function save(): void
    {
        $this->authorize($this->tripId ? 'update' : 'create', $this->tripId ? Trip::findOrFail($this->tripId) : Trip::class);

        $validated = $this->validate();

        $status = Trip::resolveStatusFor($validated['start_date'], $validated['end_date']);

        $attributes = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?: null,
            'notes' => $validated['notes'] ?: null,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'trip_category_id' => $validated['trip_category_id'],
            'buffer_alert' => $validated['buffer_alert'] ?: null,
            'status_id' => $status?->id,
        ];

        if ($this->tripId) {
            $trip = Trip::findOrFail($this->tripId);
            $trip->update($attributes);
        } else {
            $trip = Trip::create($attributes);
        }

        $trip->users()->sync($this->userIds);

        // Permanent checkpoints: replace the non-temporary set with the current selection.
        $existingPermanentIds = $trip->checkpoints()->wherePivot('is_temporary', false)->pluck('checkpoints.id');
        $trip->checkpoints()->detach($existingPermanentIds);
        $order = (int) $trip->checkpoints()->max('order');
        foreach ($this->checkpointIds as $checkpointId) {
            $trip->checkpoints()->attach($checkpointId, [
                'order' => ++$order,
                'is_temporary' => false,
            ]);
        }

        // New temporary (this-trip-only) checkpoints.
        foreach ($this->tempCheckpoints as $temp) {
            if (empty($temp['name'])) {
                continue;
            }

            $checkpoint = Checkpoint::create([
                'location' => $temp['name'],
                'address' => $temp['address'] ?: null,
                'latitude' => $temp['latitude'] ?? null,
                'longitude' => $temp['longitude'] ?? null,
                'coordinates' => ($temp['latitude'] ?? null) && ($temp['longitude'] ?? null)
                    ? $temp['latitude'].','.$temp['longitude']
                    : null,
            ]);

            $trip->checkpoints()->attach($checkpoint->id, [
                'order' => ++$order,
                'is_temporary' => true,
                'temp_location' => $temp['name'],
                'temp_address' => $temp['address'] ?: null,
            ]);
        }

        foreach ($this->plusOnes as $plusOne) {
            if (empty($plusOne['name'])) {
                continue;
            }

            PlusOne::create([
                'trip_id' => $trip->id,
                'added_by' => auth()->id(),
                'name' => $plusOne['name'],
                'email' => $plusOne['email'] ?: null,
                'phone' => $plusOne['phone'] ?: null,
            ]);
        }

        $this->showModal = false;
        session()->flash('message', $this->tripId ? 'Trip updated!' : 'Trip planned successfully!');
        $this->dispatch('trip-changed');
    }

    public function render()
    {
        return view('livewire.trips.form-modal', [
            'categories' => TripCategory::orderBy('name')->get(),
            'users' => User::orderBy('name')->get(),
            'permanentCheckpoints' => Checkpoint::whereNotNull('folder_id')
                ->whereDoesntHave('trips', fn ($q) => $q->where('trip_checkpoints.is_temporary', true))
                ->orderBy('location')
                ->get(),
        ]);
    }
}
