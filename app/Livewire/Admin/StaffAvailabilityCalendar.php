<?php

namespace App\Livewire\Admin;

use App\Events\NotificationCreated;
use App\Models\UnavailabilityPeriod;
use App\Models\User;
use App\Models\UserNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

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

    public function staffThreeOrMoreUnavailableSendNotification()
    {
        // if there are 3 or more staff unavailable on the same day, send a notification to the admin.
        $startDate = Carbon::today()->startOfDay();
        $endDate = Carbon::tomorrow()->endOfDay();

        // Iterate each day in the window (today and tomorrow)
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dayStart = $date->copy()->startOfDay();
            $dayEnd = $date->copy()->endOfDay();

            $count = UnavailabilityPeriod::where(function ($q) use ($dayStart, $dayEnd) {
                $q->whereBetween('start_date', [$dayStart, $dayEnd])
                    ->orWhereBetween('end_date', [$dayStart, $dayEnd])
                    ->orWhere(function ($q2) use ($dayStart, $dayEnd) {
                        $q2->where('start_date', '<', $dayStart)
                            ->where('end_date', '>', $dayEnd);
                    });
            })->distinct('user_id')->count('user_id');

            if ($count >= 3) {
                $admins = User::whereHas('role', fn ($q) => $q->where('name', 'Admin'))->get();

                foreach ($admins as $admin) {

                    $exists = UserNotification::where('user_id', $admin->id)
                        ->where('type', 'staff_shortage')
                        ->whereBetween('created_at', [$dayStart, $dayEnd])
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    $message = "{$count} staff unavailable on ".$date->format('l j M');
                    $notification = UserNotification::create([
                        'user_id' => $admin->id,
                        'from_user_id' => Auth::id() ?? null,
                        'title' => 'Staff Shortage Alert',
                        'message' => $message,
                        'type' => 'staff_shortage',
                        'action_url' => route('admin.staff-unavailability'),
                    ]);

                    try {
                        broadcast(new NotificationCreated($notification));
                    } catch (\Throwable $e) {
                        // swallow broadcasting errors — notification record still exists
                        \Log::error('Failed broadcasting staff shortage notification', ['error' => $e->getMessage()]);
                    }
                }
            }
        }
    }

    public function getAllUsersProperty()
    {
        return User::with('unavailabilityPeriods')
            ->whereHas('role', fn ($q) => $q->whereIn('name', [
                'Admin', 'Staff', 'Chef', 'Family Member', 'The Andersons',
            ]))
            ->get();
    }

    public function getFilteredUsersProperty()
    {
        return $this->allUsers->filter(function ($user) {
            // Filter by name
            if ($this->filterName && ! str_contains(
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
                if (! $hasMatch) {
                    return false;
                }
            }

            // Only show users who have at least one period
            return $user->unavailabilityPeriods->count() > 0;
        });
    }

    public function openCreate()
    {
        $this->reset(['editingId', 'startDate', 'startTime', 'endDate', 'endTime', 'description', 'selectedUserId']);
        $this->startTime = '00:00';
        $this->endTime = '23:59';
        $this->showModal = true;
    }

    public function openEdit($periodId)
    {
        $period = UnavailabilityPeriod::findOrFail($periodId);

        if ($period->end_date->isPast()) {
            $this->dispatch('toast', title: 'Read-only Period', message: 'Past unavailability periods cannot be edited.', type: 'error');

            return;
        }

        $this->editingId = $period->id;
        $this->selectedUserId = $period->user_id;
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
            'selectedUserId' => 'required|exists:users,id',
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

        // Check for duplicate exact period for the selected user
        $duplicateQuery = UnavailabilityPeriod::where('user_id', $this->selectedUserId)
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

            if ($period->end_date->isPast()) {
                $this->dispatch('toast', title: 'Read-only Period', message: 'Past unavailability periods cannot be updated.', type: 'error');

                return;
            }

            $period->update([
                'user_id' => $this->selectedUserId,
                'start_date' => $start,
                'end_date' => $end,
                'description' => $this->description,
            ]);
            $this->dispatch('toast', title: 'Period Updated', message: 'The unavailability period has been updated.', type: 'success');
        } else {
            UnavailabilityPeriod::create([
                'user_id' => $this->selectedUserId,
                'start_date' => $start,
                'end_date' => $end,
                'description' => $this->description,
            ]);
            $this->dispatch('toast', title: 'Period Added', message: 'The unavailability period has been added.', type: 'success');
        }

        $this->staffThreeOrMoreUnavailableSendNotification();

        $this->showModal = false;
        $this->reset(['editingId', 'startDate', 'startTime', 'endDate', 'endTime', 'description', 'selectedUserId']);
    }

    public function delete($id)
    {
        $period = UnavailabilityPeriod::findOrFail($id);

        if ($period->end_date->isPast()) {
            $this->dispatch('toast', title: 'Read-only Period', message: 'Past unavailability periods cannot be deleted.', type: 'error');

            return;
        }

        $period->delete();
        $this->dispatch('toast', title: 'Period Removed', message: 'The unavailability period has been removed.', type: 'success');
    }

    public function clearFilters()
    {
        $this->filterName = '';
        $this->filterDate = null;
    }

    public function render()
    {
        return view('livewire.admin.staff-availability-calendar')
            ->layout('components.layouts.app', ['title' => 'Staff Availability']);
    }
}
