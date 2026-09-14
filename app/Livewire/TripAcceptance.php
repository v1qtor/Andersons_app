<?php

namespace App\Livewire;

use App\Models\PlusOne;
use Livewire\Component;

class TripAcceptance extends Component
{
    public $trip;

    public $showModal = false;

    public $plusOnes = [];

    public $plusOneCount = 0;

    public $acceptTrip = false;

    public function mount($trip)
    {
        $this->trip = $trip;
        $this->acceptTrip = auth()->user()->trips()->where('trip_id', $trip->id)->exists();
    }

    public function acceptTripWithPlusOnes()
    {
        $user = auth()->user();

        // Attach user to trip if not already attached
        if (! $user->trips()->where('trip_id', $this->trip->id)->exists()) {
            $user->trips()->attach($this->trip->id, ['is_organizer' => false]);
        }

        // Add plus-ones
        foreach (array_filter($this->plusOnes) as $plusOne) {
            if (! empty($plusOne['name'])) {
                PlusOne::create([
                    'trip_id' => $this->trip->id,
                    'added_by' => $user->id,
                    'name' => $plusOne['name'],
                    'email' => $plusOne['email'] ?? null,
                    'phone' => $plusOne['phone'] ?? null,
                ]);
            }
        }

        $this->acceptTrip = true;
        $this->dispatch('toast', message: 'You accepted the trip and plus-ones were added!', type: 'success');
        $this->dispatch('refresh-dashboard');
    }

    public function rejectTrip()
    {
        $user = auth()->user();

        // Detach user from trip
        if ($user->trips()->where('trip_id', $this->trip->id)->exists()) {
            $user->trips()->detach($this->trip->id);

            // Also delete any plus-ones they added
            PlusOne::where('trip_id', $this->trip->id)
                ->where('added_by', $user->id)
                ->delete();
        }

        $this->acceptTrip = false;
        $this->plusOnes = [];
        $this->dispatch('toast', message: 'You declined the trip.', type: 'success');
        $this->dispatch('refresh-dashboard');
    }

    public function addPlusOne()
    {
        $this->plusOnes[] = ['name' => '', 'email' => '', 'phone' => ''];
    }

    public function removePlusOne($index)
    {
        unset($this->plusOnes[$index]);
        $this->plusOnes = array_values($this->plusOnes);
    }

    public function render()
    {
        return view('livewire.trip-acceptance');
    }
}
