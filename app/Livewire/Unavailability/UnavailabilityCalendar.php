<?php

namespace App\Livewire\Unavailability;

use App\Models\UnavailabilityPeriod;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

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
        $this->endTime = '23:59';
        $this->showModal = true;
    }

    public function openEdit($periodId)
    {
        $period = UnavailabilityPeriod::findOrFail($periodId);

        if ($period->user_id !== Auth::id()) {
            abort(403);
        }

        if ($period->end_date->isPast()) {
            $this->dispatch('toast', title: 'Read-only Period', message: 'Past unavailability periods cannot be edited.', type: 'error');

            return;
        }

        $this->editingId = $period->id;
        $this->startDate = $period->start_date->format('Y-m-d');
        $this->startTime = $period->start_date->format('H:i');
        $this->endDate = $period->end_date->format('Y-m-d');
        $this->endTime = $period->end_date->format('H:i');
        $this->description = $period->description ?? '';
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'startDate' => 'required|date',
            'startTime' => 'required',
            'endDate' => 'required|date|after_or_equal:startDate',
            'endTime' => 'required',
            'description' => 'nullable|string|max:255',
        ]);

        $startDateTime = $this->startDate.' '.$this->startTime.':00';
        $endDateTime = $this->endDate.' '.$this->endTime.':00';
        $start = Carbon::parse($startDateTime);
        $end = Carbon::parse($endDateTime);

        if ($start->lt(now())) {
            $this->addError('startTime', 'Start date and time cannot be in the past.');

            return;
        }

        if ($end->lte($start)) {
            $this->addError('endTime', 'End date and time must be after the start date and time.');

            return;
        }

        // Check for duplicate exact period for this user
        $duplicateQuery = UnavailabilityPeriod::where('user_id', Auth::id())
            ->where('start_date', $start)
            ->where('end_date', $end);

        if ($this->editingId) {
            $duplicateQuery->where('id', '!=', $this->editingId);
        }

        if ($duplicateQuery->exists()) {
            $this->addError('duplicate', 'this period already exist. Select a new one or change the already available one.');

            return;
        }

        if ($this->editingId) {
            $period = UnavailabilityPeriod::findOrFail($this->editingId);
            if ($period->user_id !== Auth::id()) {
                abort(403);
            }

            if ($period->end_date->isPast()) {
                $this->dispatch('toast', title: 'Read-only Period', message: 'Past unavailability periods cannot be updated.', type: 'error');

                return;
            }

            $period->update([
                'start_date' => $start,
                'end_date' => $end,
                'description' => $this->description,
            ]);
            $this->dispatch('toast', title: 'Period Updated', message: 'Your unavailability period has been updated.', type: 'success');
        } else {
            UnavailabilityPeriod::create([
                'user_id' => Auth::id(),
                'start_date' => $start,
                'end_date' => $end,
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

        if ($period->end_date->isPast()) {
            $this->dispatch('toast', title: 'Read-only Period', message: 'Past unavailability periods cannot be deleted.', type: 'error');

            return;
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
