<?php

namespace App\Livewire\Admin;

use App\Models\UnavailabilityPeriod;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Carbon\Carbon;

class StaffAvailabilityCalendar extends Component
{
    public $showModal = false;
    public $editingId = null;

    public $selectedUserId = '';
    public $startDate = '';
    public $startTime = '00:00';
    public $endDate = '';
    public $endTime = '23:59';
    public $description = '';

    // Filters
    public $filterName = '';
    public $filterDate = '';

    public function mount()
    {
        if (Auth::user()->role?->name !== 'Admin') {
            abort(403);
        }
    }

    // public function dashboardUpcomingAvailability(){
    //     // for the upcoming 2 days, show who is unavailable and when
    //     $startDate  = Carbon::today();
    //     $endDate = Carbon::tomorrow()->endOfDay();


    // }
    
    // public function staffThreeOrMoreUnavailableSendNotification(){
    //     // if there are 3 or more staff unavailable on the same day, send a notification to the admin.

    // }

    public function getAllUsersProperty()
    {
        return User::with('unavailabilityPeriods')
            ->whereHas('role', fn($q) => $q->whereIn('name', [
                'Admin', 'Staff', 'Chef', 'Family Member', 'The Andersons'
            ]))
            ->get();
    }

    public function getFilteredUsersProperty()
    {
        return $this->allUsers->filter(function ($user) {
            // Filter by name
            if ($this->filterName && !str_contains(
                strtolower($user->name),
                strtolower($this->filterName)
            )) {
                return false;
            }

            // Filter by date — only show users who have a period on that date
            if ($this->filterDate) {
                $hasMatch = $user->unavailabilityPeriods->contains(function ($period) {
                    return Carbon::parse($this->filterDate)->between(
                        $period->start_date->copy()->startOfDay(),
                        $period->end_date->copy()->endOfDay()
                    );
                });
                if (!$hasMatch) return false;
            }

            // Only show users who have at least one period
            return $user->unavailabilityPeriods->count() > 0;
        });
    }

    public function openCreate()
    {
        $this->reset(['editingId', 'startDate', 'startTime', 'endDate', 'endTime', 'description', 'selectedUserId']);
        $this->startTime = '00:00';
        $this->endTime   = '23:59';
        $this->showModal = true;
    }

    public function openEdit($periodId)
    {
        $period = UnavailabilityPeriod::findOrFail($periodId);
        $this->editingId      = $period->id;
        $this->selectedUserId = $period->user_id;
        $this->startDate      = $period->start_date->format('Y-m-d');
        $this->startTime      = $period->start_date->format('H:i');
        $this->endDate        = $period->end_date->format('Y-m-d');
        $this->endTime        = $period->end_date->format('H:i');
        $this->description    = $period->description ?? '';
        $this->showModal      = true;
    }

    public function save()
    {
        $this->validate([
            'selectedUserId' => 'required|exists:users,id',
            'startDate'      => 'required|date',
            'startTime'      => 'required',
            'endDate'        => 'required|date|after_or_equal:startDate',
            'endTime'        => 'required',
            'description'    => 'nullable|string|max:255',
        ]);

        $startDateTime = $this->startDate . ' ' . $this->startTime . ':00';
        $endDateTime   = $this->endDate . ' ' . $this->endTime . ':00';

        if ($this->editingId) {
            UnavailabilityPeriod::findOrFail($this->editingId)->update([
                'user_id'     => $this->selectedUserId,
                'start_date'  => $startDateTime,
                'end_date'    => $endDateTime,
                'description' => $this->description,
            ]);
            $this->dispatch('toast', title: 'Period Updated', message: 'The unavailability period has been updated.', type: 'success');
        } else {
            UnavailabilityPeriod::create([
                'user_id'     => $this->selectedUserId,
                'start_date'  => $startDateTime,
                'end_date'    => $endDateTime,
                'description' => $this->description,
            ]);
            $this->dispatch('toast', title: 'Period Added', message: 'The unavailability period has been added.', type: 'success');
        }

        $this->showModal = false;
        $this->reset(['editingId', 'startDate', 'startTime', 'endDate', 'endTime', 'description', 'selectedUserId']);
    }

    public function delete($id)
    {
        UnavailabilityPeriod::findOrFail($id)->delete();
        $this->dispatch('toast', title: 'Period Removed', message: 'The unavailability period has been removed.', type: 'success');
    }

    public function render()
    {
        return view('livewire.admin.staff-availability-calendar')
            ->layout('components.layouts.app', ['title' => 'Staff Availability']);
    }
}