<?php

namespace App\Livewire\Admin;

use App\Models\UnavailabilityPeriod;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Carbon\Carbon;

class StaffAvailabilityCalendar extends Component
{
    public $currentWeekStart;
    public $showModal = false;
    public $editingId = null;

    public $selectedUserId = '';
    public $startDate = '';
    public $endDate = '';
    public $description = '';

    public function mount()
    {
        if (Auth::user()->role?->name !== 'Admin') {
            abort(403);
        }
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

    public function getAllUsersProperty()
    {
        // All roles except Admin can have unavailability
        return User::with('unavailabilityPeriods')
            ->whereHas('role', fn($q) => $q->whereIn('name', [
                'Staff', 'Chef', 'Family Member', 'The Andersons'
            ]))
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

    public function openCreate($date = null, $userId = null)
    {
        $this->reset(['editingId', 'startDate', 'endDate', 'description', 'selectedUserId']);
        $this->startDate      = $date ?? '';
        $this->endDate        = $date ?? '';
        $this->selectedUserId = $userId ?? '';
        $this->showModal      = true;
    }

    public function openEdit($periodId)
    {
        $period = UnavailabilityPeriod::findOrFail($periodId);

        $this->editingId      = $period->id;
        $this->selectedUserId = $period->user_id;
        $this->startDate      = $period->start_date->format('Y-m-d');
        $this->endDate        = $period->end_date->format('Y-m-d');
        $this->description    = $period->description ?? '';
        $this->showModal      = true;
    }

    public function save()
    {
        $this->validate([
            'selectedUserId' => 'required|exists:users,id',
            'startDate'      => 'required|date',
            'endDate'        => 'required|date|after_or_equal:startDate',
            'description'    => 'nullable|string|max:255',
        ]);

        if ($this->editingId) {
            UnavailabilityPeriod::findOrFail($this->editingId)->update([
                'user_id'     => $this->selectedUserId,
                'start_date'  => $this->startDate,
                'end_date'    => $this->endDate,
                'description' => $this->description,
            ]);
        } else {
            UnavailabilityPeriod::create([
                'user_id'     => $this->selectedUserId,
                'start_date'  => $this->startDate,
                'end_date'    => $this->endDate,
                'description' => $this->description,
            ]);
        }

        $this->showModal = false;
        $this->reset(['editingId', 'startDate', 'endDate', 'description', 'selectedUserId']);
        session()->flash('success', 'Period saved.');
    }

    public function delete($id)
    {
        UnavailabilityPeriod::findOrFail($id)->delete();
        session()->flash('success', 'Period removed.');
    }

    public function getPeriodForDate($userId, $date)
    {
        $user = $this->allUsers->firstWhere('id', $userId);
        if (!$user) return null;

        return $user->unavailabilityPeriods->first(function ($period) use ($date) {
            return Carbon::parse($date)->between(
                $period->start_date->copy()->startOfDay(),
                $period->end_date->copy()->endOfDay()
            );
        });
    }

    public function render()
    {
        return view('livewire.admin.staff-availability-calendar')
            ->layout('components.layouts.app', ['title' => 'Staff Availability']);
    }
}