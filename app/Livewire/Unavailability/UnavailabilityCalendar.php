<?php

namespace App\Livewire\Unavailability;

use App\Models\UnavailabilityPeriod;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Carbon\Carbon;

class UnavailabilityCalendar extends Component
{
    public $showModal = false;
    public $editingId = null;
    public $startDate = ''
    public $startTime = '00:00';
    public $endDate = '';
    public $endTime = '23:59';
    public $description = '';

    public function getPeriodsProperty(){
        return Auth::user() //Modal
        ->unavailabilityPeriods() // rekationship in user model
        ->orderBy('start_date')
        ->get();
    }

    public function openCreate(){
        $this->reset(['editingId', 'startDate', 'startTime', 'endDate', 'endTime', 'description']);
        $this->showModal = true;
        $this->startTime = '00:00';
        $this->endTime = '23:59';
    }

    public function openEdit($periodId){
        $period = UnavailabilityPeriod::findOrFail($periodId);
        if ($period->user_id !== Auth::id()){
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

    public function save(){

    }

    public function delete(){

    }

    public function render(){

    }
}