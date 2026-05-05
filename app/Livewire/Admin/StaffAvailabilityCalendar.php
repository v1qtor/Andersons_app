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

    public function getFileteredUsersProperty(){
        return $this->allUsers->filter(function ($user) {
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
    public function render(){
        return view();
    }
}