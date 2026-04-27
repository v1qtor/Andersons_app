<?php

namespace App\Livewire\Unavailability;

use App\Models\UnavailabilityPeriod;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Carbon\Carbon;

class UnavailabilityCalendar extends Component
{
    public $currentWeekStart;
    public $showModal = false;
    public $editingId = null;

    public $startDate = '';
    public $endDate = '';
    public $description = '';

    public function mount()
    {
        $this->currentWeekStart = Carbon::now()->startOfWeek()->format('Y-m-d');
    }

    public function getWeekDaysProperty()
    {
        $start = Carbon::parse($this->currentWeekStart);
        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $days[] = $start->copy()->addDays($i)->format('Y-m-d');
        }
        return $days;
    }

    public function getPeriodsProperty()
    {
        return Auth::user()
            ->unavailabilityPeriods()
            ->orderBy('start_date')
            ->get();
    }

    public function previousWeek()
    {
        $this->currentWeekStart = Carbon::parse($this->currentWeekStart)
            ->subWeek()->format('Y-m-d');
    }

    public function nextWeek()
    {
        $this->currentWeekStart = Carbon::parse($this->currentWeekStart)
            ->addWeek()->format('Y-m-d');
    }

    public function goToCurrentWeek()
    {
        $this->currentWeekStart = Carbon::now()->startOfWeek()->format('Y-m-d');
    }

    public function openCreate($date = null)
    {
        $this->reset(['editingId', 'startDate', 'endDate', 'description']);
        $this->startDate = $date ?? '';
        $this->endDate   = $date ?? '';
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
        $this->endDate     = $period->end_date->format('Y-m-d');
        $this->description = $period->description ?? '';
        $this->showModal   = true;
    }

    public function save()
    {
        $this->validate([
            'startDate'   => 'required|date',
            'endDate'     => 'required|date|after_or_equal:startDate',
            'description' => 'nullable|string|max:255',
        ]);

        if ($this->editingId) {
            $period = UnavailabilityPeriod::findOrFail($this->editingId);

            if ($period->user_id !== Auth::id()) {
                abort(403);
            }

            $period->update([
                'start_date'  => $this->startDate,
                'end_date'    => $this->endDate,
                'description' => $this->description,
            ]);

            session()->flash('success', 'Period updated.');
        } else {
            UnavailabilityPeriod::create([
                'user_id'     => Auth::id(),
                'start_date'  => $this->startDate,
                'end_date'    => $this->endDate,
                'description' => $this->description,
            ]);

            session()->flash('success', 'Period added.');
        }

        $this->showModal = false;
        $this->reset(['editingId', 'startDate', 'endDate', 'description']);
    }

    public function delete($id)
    {
        $period = UnavailabilityPeriod::findOrFail($id);

        if ($period->user_id !== Auth::id()) {
            abort(403);
        }

        $period->delete();
        session()->flash('success', 'Period removed.');
    }

    public function getPeriodForDate($date)
    {
        return $this->periods->first(function ($period) use ($date) {
            return Carbon::parse($date)->between(
                $period->start_date->copy()->startOfDay(),
                $period->end_date->copy()->endOfDay()
            );
        });
    }

    public function render()
    {
        return view('livewire.unavailability.unavailability-calendar')
            ->layout('components.layouts.app', ['title' => 'My Unavailability']);
    }
}