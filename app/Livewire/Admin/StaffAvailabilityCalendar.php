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
    public $startDate = '';
    public $endDare = '';
    public $startTime = '00:00';
    public $endTime = '23:59';
    public $description = '';
    public $filterName = '';
    public $filterDate = '';
    public $editingId = null;
    public $selectedUserId = '';

    public function mount(){
        if(Auth::user()->role!=='Admin'){
            abort(403);
        }
    }

    public function getAllUsersProperty(){
        return User::with('unavailabilityPeriods')
        ->whereHas('role', fn($q)=>$q->whereIn('name', [
            'Admin', 'Staff', 'Chef', 'Family Member', 'The Andersons'
        ]))->get();
    }

    public function getFilteredUsersProperty(){
        return $this->allUsers->filter(function (   $user) {
            if ($this->filterName && !str_contains(
                strtolower($user->name),
                strtolower($this->filterName)
            )) {
                return false;
            }

            if ($this->filterDate) {
                $hasMatch = $user->unavailabilityPeriods->contains(function ($period) {
                    return Carbon::parse($this->filterDate)->between(
                        $period->start_date->copy()->startOfDay(),
                        $period->end_date->copy()->endOfDay()
                    );
                });
                if (!$hasMatch) return false;
            }
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

    public function openEdit($periodId){
        $period = UnavailabilityPeriod::findOrfail($periodId);
        $this->editingId = $period->id;
        $this->startDate = $period->start_date->format('Y-m-d');
        $this->startTime = $period->start_date->format('H:i');
        $this->endDate   = $period->end_date->format('Y-m-d');
        $this->endTime   = $period->end_date->format('H:i');
        $this->description = $period->description ?? '';
        $this->selectedUserId = $period->user_id;
        $this->showModal = true;
    }
    public function save(){
        $this->validate({
            'selectedUserId' => 'required|exists:users,id',
            'startDate'      => 'required|date',
            'startTime'      => 'required',
            'endDate'        => 'required|date|after_or_equal:startDate',
            'endTime'        => 'required',
            'description'    => 'nullable|string|max:255',
        ]);
        
        $startDateTime = $this->startDate . ' ' . $this->startTime . ':00';
        $endDateTime   = $this->endDate . ' ' . $this->endTime . ':00';

        if($htis->edittingId) {
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

    public function delete($periodId) {
        UnavailabilityPeriod::findOrFail($periodId)->delete();
        $this->dispatch('toast', title: 'Period Deleted', message: 'The unavailability period has been deleted.', type: 'success');
    }

    public function render(){
         return view('livewire.admin.staff-availability-calendar')
            ->layout('components.layouts.app', ['title' => 'Staff Availability']);
    }
}