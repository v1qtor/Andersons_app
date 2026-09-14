<?php

namespace App\Livewire\Trips;

use App\Models\Status;
use App\Models\Trip;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

/*
| Trips page: search/filter/paginate the household's trips and render
| a TripCard for each one. Owns the create-trip trigger; editing and
| all other per-trip actions live in TripFormModal/TripCard.
*/
#[Layout('components.layouts.app')]
class TripManager extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = 'all';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->statusFilter = 'all';
        $this->resetPage();
    }

    #[On('trip-changed')]
    public function refreshList(): void
    {
        // No-op: dispatching this event is enough to make Livewire
        // re-render this component and re-run the query in render().
    }

    public function getTrips()
    {
        // Each TripCard loads its own full copy by ID, so this query only
        // needs enough to filter, sort and paginate.
        $query = Trip::query();

        if ($this->statusFilter !== 'all') {
            $query->whereHas('status', fn ($q) => $q->where('name', $this->statusFilter));
        }

        if ($this->search !== '') {
            $term = '%'.$this->search.'%';
            $query->where(fn ($q) => $q->where('name', 'like', $term)->orWhere('description', 'like', $term));
        }

        return $query->orderBy('start_date', 'desc')->paginate(5);
    }

    public function render()
    {
        return view('livewire.trips.manager', [
            'trips' => $this->getTrips(),
            'statusOptions' => Status::where('type', 'trip')->orderBy('name')->pluck('name'),
            'canManageTrips' => auth()->user()->can('create', Trip::class),
        ]);
    }
}
