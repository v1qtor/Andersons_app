<?php

namespace App\Livewire\Unavailability;

use App\Models\UnavailabilityPeriod;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Carbon\Carbon;

// This component has been removed and is no longer in use.
class UnavailabilityCalendar extends Component
{
    public $showModal = false;
    public $editingId = null;

    public $startDate = '';
    public $startTime = '00:00';
    public $endDate = '';
    public $endTime = '23:59';
    public $description = '';

    public function getPeriodsProperty()
    {
        return Auth::user()
            ->unavailabilityPeriods()
            ->orderBy('start_date')
            ->get();
    }

    public function openCreate()
    {
        $this->reset(['editingId', 'startDate', 'startTime', 'endDate', 'endTime', 'description']);
        $this->startTime = '00:00';
        $this->endTime   = '23:59';
        $this->showModal = true;
    }

    public function openEdit($periodId)
    {
        $period = UnavailabilityPeriod::findOrFail($periodId);

        if ($period->user_id !== Auth::id()) {
            abort(403);
        }

        $this->editingId   = $period->id;
        $this->startDate   = $period->start_date->format('Y-m-d');
        $this->startTime   = $period->start_date->format('H:i');
        $this->endDate     = $period->end_date->format('Y-m-d');
        $this->endTime     = $period->end_date->format('H:i');
        $this->description = $period->description ?? '';
        $this->showModal   = true;
    }

    public function save()
    {
        $this->validate([
            'startDate'   => 'required|date',
            'startTime'   => 'required',
            'endDate'     => 'required|date|after_or_equal:startDate',
            'endTime'     => 'required',
            'description' => 'nullable|string|max:255',
        ]);

        $startDateTime = $this->startDate . ' ' . $this->startTime . ':00';
        $endDateTime   = $this->endDate . ' ' . $this->endTime . ':00';

        if ($this->editingId) {
            $period = UnavailabilityPeriod::findOrFail($this->editingId);
            if ($period->user_id !== Auth::id()) {
                abort(403);
            }
            $period->update([
                'start_date'  => $startDateTime,
                'end_date'    => $endDateTime,
                'description' => $this->description,
            ]);
            $this->dispatch('toast', title: 'Period Updated', message: 'Your unavailability period has been updated.', type: 'success');
        } else {
            UnavailabilityPeriod::create([
                'user_id'     => Auth::id(),
                'start_date'  => $startDateTime,
                'end_date'    => $endDateTime,
                'description' => $this->description,
            ]);
            $this->dispatch('toast', title: 'Period Added', message: 'Your unavailability period has been added.', type: 'success');
        }

        $this->showModal = false;
        $this->reset(['editingId', 'startDate', 'startTime', 'endDate', 'endTime', 'description']);
    }

    public function delete($id)
    {
        $period = UnavailabilityPeriod::findOrFail($id);
        if ($period->user_id !== Auth::id()) {
            abort(403);
        }
        $period->delete();
        $this->dispatch('toast', title: 'Period Removed', message: 'The unavailability period has been removed.', type: 'success');
    }

    public function render()
    {
       return view('livewire.unavailability.unavailability-calendar')
       ->layout('components.layouts.app', ['title' => 'My Unavailability']);
    }
}
