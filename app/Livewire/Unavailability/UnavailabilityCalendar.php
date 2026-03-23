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

    // Form fields
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
        return Auth::user()->unavailabilityPeriods()
            ->orderBy('startDate')
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
        $this->showModal = true;
    }

    public function openEdit(UnavailabilityPeriod $period)
    {
        if ($period->userId !== Auth::id()) {
            abort(403);
        }

        $this->editingId = $period->unavailabilityPeriodId;
        $this->startDate = $period->startDate->format('Y-m-d');
        $this->endDate = $period->endDate->format('Y-m-d');
        $this->description = $period->description ?? '';
        $this->showModal = true;
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
            if ($period->userId !== Auth::id()) {
                abort(403);
            }
            $period->update([
                'startDate'   => $this->startDate,
                'endDate'     => $this->endDate,
                'description' => $this->description,
            ]);
        } else {
            Auth::user()->unavailabilityPeriods()->create([
                'startDate'   => $this->startDate,
                'endDate'     => $this->endDate,
                'description' => $this->description,
            ]);
        }

        $this->showModal = false;
        $this->reset(['editingId', 'startDate', 'endDate', 'description']);
        session()->flash('success', $this->editingId ? 'Period updated.' : 'Period added.');
    }

    public function delete($id)
    {
        $period = UnavailabilityPeriod::findOrFail($id);
        if ($period->userId !== Auth::id()) {
            abort(403);
        }
        $period->delete();
        session()->flash('success', 'Period removed.');
    }

    // Check if a date falls within any unavailability period
    public function getPeriodForDate($date)
    {
        return $this->periods->first(function ($period) use ($date) {
            return Carbon::parse($date)->between(
                $period->startDate->startOfDay(),
                $period->endDate->endOfDay()
            );
        });
    }

    public function render()
    {
        return view('livewire.unavailability.unavailability-calendar')
            ->layout('components.layouts.app', ['title' => 'My Unavailability']);
    }
}
