<?php

namespace App\Livewire;

use App\Models\Task;
use App\Models\UnavailabilityPeriod;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Unavailability extends Component
{
    public string $startDate = '';
    public string $startTime = '';
    public string $endDate = '';
    public string $endTime = '';
    public string $description = '';

    public bool $showForm = false;
    public ?int $deletingId = null;
    public bool $showDeleteModal = false;

    public function mount(): void
    {
        // Access control is handled by middleware, but double-check here
        $role = Auth::user()->role?->name;
        if (! in_array($role, ['Staff', 'Chef', 'Admin'])) {
            abort(403);
        }
    }

    public function openForm(): void
    {
        $this->reset(['startDate', 'startTime', 'endDate', 'endTime', 'description']);
        $this->resetValidation();
        $this->showForm = true;
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->reset(['startDate', 'startTime', 'endDate', 'endTime', 'description']);
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->validate([
            'startDate' => 'required|date|after_or_equal:today',
            'startTime' => 'required|date_format:H:i',
            'endDate'   => 'required|date|after_or_equal:startDate',
            'endTime'   => 'required|date_format:H:i',
            'description' => 'nullable|string|max:500',
        ]);

        $start = Carbon::parse($this->startDate . ' ' . $this->startTime);
        $end   = Carbon::parse($this->endDate   . ' ' . $this->endTime);

        if ($end->lte($start)) {
            $this->addError('endTime', 'End date/time must be after start date/time.');
            return;
        }

        // Check for task conflicts: any task assigned to the user where
        // the task's time range overlaps with the unavailability period.
        $conflict = Task::whereHas('users', function ($q) {
                $q->where('users.id', Auth::id());
            })
            ->where('is_complete', false)
            ->where(function ($q) use ($start, $end) {
                // Overlap: task starts before unavailability ends AND task ends after unavailability starts
                $q->where('start_date', '<', $end)
                  ->where(function ($q2) use ($start) {
                      $q2->where('end_date', '>', $start)
                         ->orWhere(function ($q3) use ($start) {
                             // If end_date is null, use start_date + 1 hour as implicit end
                             $q3->whereNull('end_date')
                                ->whereRaw("datetime(start_date, '+1 hour') > ?", [$start->toDateTimeString()]);
                         });
                  });
            })
            ->first();

        if ($conflict) {
            $this->addError('startDate', 'You have a task "' . $conflict->title . '" on ' . Carbon::parse($conflict->start_date)->format('d M Y, H:i') . ' that conflicts with this unavailability period.');
            return;
        }

        UnavailabilityPeriod::create([
            'user_id'     => Auth::id(),
            'start_date'  => $start,
            'end_date'    => $end,
            'description' => $this->description ?: null,
        ]);

        $this->closeForm();
        $this->dispatch('toast', message: 'Unavailability period added successfully.', type: 'success');
    }

    public function confirmDelete(int $id): void
    {
        $period = UnavailabilityPeriod::find($id);

        if (! $period || $period->user_id !== Auth::id()) {
            return;
        }

        // Only allow deleting future unavailabilities
        if ($period->start_date->isPast()) {
            $this->dispatch('toast', message: 'You can only delete future unavailability periods.', type: 'error');
            return;
        }

        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if (! $this->deletingId) {
            return;
        }

        $period = UnavailabilityPeriod::find($this->deletingId);

        if ($period && $period->user_id === Auth::id() && ! $period->start_date->isPast()) {
            $period->delete();
            $this->dispatch('toast', message: 'Unavailability period deleted.', type: 'success');
        }

        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    public function render()
    {
        $periods = UnavailabilityPeriod::where('user_id', Auth::id())
            ->orderBy('start_date', 'asc')
            ->get();

        $upcoming = $periods->filter(fn ($p) => $p->end_date->isFuture() || $p->start_date->isFuture());
        $past     = $periods->filter(fn ($p) => $p->end_date->isPast());

        return view('livewire.unavailability', [
            'upcoming' => $upcoming,
            'past'     => $past,
        ]);
    }
}
