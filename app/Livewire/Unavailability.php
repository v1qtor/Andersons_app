<?php

namespace App\Livewire;

use App\Models\Task;
use App\Models\UnavailabilityPeriod;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Unavailability extends Component
{
    // ─── Form fields ──────────────────────────────────────────
    public string $startDate = '';
    public string $startTime = '';
    public string $endDate = '';
    public string $endTime = '';
    public string $description = '';

    public bool $showForm = false;

    // ─── Edit ─────────────────────────────────────────────────
    public ?int $editingId = null;

    // ─── Delete ───────────────────────────────────────────────
    public ?int $deletingId = null;
    public bool $showDeleteModal = false;

    // ─── Filter ───────────────────────────────────────────────
    public ?int $filterUserId = null;   // null = everyone
    public bool $myOnly = false;        // "My Unavailabilities" shortcut

    private function isAdmin(): bool
    {
        return Auth::user()->role?->name === 'Admin';
    }

    public function mount(): void
    {
        $role = Auth::user()->role?->name;
        if (! in_array($role, ['Staff', 'Chef', 'Admin'])) {
            abort(403);
        }
    }

    // ─── Filter methods ───────────────────────────────────────

    public function setFilter(?int $userId): void
    {
        $this->filterUserId = $userId;
        $this->myOnly = false;
    }

    public function setMyOnly(): void
    {
        $this->myOnly = true;
        $this->filterUserId = null;
    }

    // ─── Form open/close ──────────────────────────────────────

    public function openForm(): void
    {
        $this->editingId = null;
        $this->reset(['startDate', 'startTime', 'endDate', 'endTime', 'description']);
        $this->resetValidation();
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        $period = UnavailabilityPeriod::findOrFail($id);

        // Admins can edit anyone; others only their own
        if (! $this->isAdmin() && $period->user_id !== Auth::id()) {
            return;
        }

        $this->editingId = $id;
        $this->startDate = $period->start_date->format('Y-m-d');
        $this->startTime = $period->start_date->format('H:i');
        $this->endDate   = $period->end_date->format('Y-m-d');
        $this->endTime   = $period->end_date->format('H:i');
        $this->description = $period->description ?? '';
        $this->resetValidation();
        $this->showForm = true;
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->editingId = null;
        $this->reset(['startDate', 'startTime', 'endDate', 'endTime', 'description']);
        $this->resetValidation();
    }

    // ─── Save (create or update) ──────────────────────────────

    public function save(): void
    {
        $isAdmin = $this->isAdmin();

        $rules = [
            'startDate'   => 'required|date',
            'startTime'   => 'required|date_format:H:i',
            'endDate'     => 'required|date|after_or_equal:startDate',
            'endTime'     => 'required|date_format:H:i',
            'description' => 'nullable|string|max:500',
        ];

        // Non-admins cannot add past unavailabilities
        if (! $isAdmin) {
            $rules['startDate'] = 'required|date|after_or_equal:today';
        }

        $this->validate($rules);

        $start = Carbon::parse($this->startDate . ' ' . $this->startTime);
        $end   = Carbon::parse($this->endDate   . ' ' . $this->endTime);

        if ($end->lte($start)) {
            $this->addError('endTime', 'End date/time must be after start date/time.');
            return;
        }

        // Determine which user this period belongs to
        $ownerId = $this->editingId
            ? UnavailabilityPeriod::find($this->editingId)?->user_id
            : Auth::id();

        // Task conflict check (skip for admins editing others' records)
        if ($ownerId === Auth::id() || ! $isAdmin) {
            $conflictQuery = Task::whereHas('users', function ($q) use ($ownerId) {
                    $q->where('users.id', $ownerId);
                })
                ->where('is_complete', false)
                ->where(function ($q) use ($start, $end) {
                    $q->where('start_date', '<', $end)
                      ->where(function ($q2) use ($start) {
                          $q2->where('end_date', '>', $start)
                             ->orWhere(function ($q3) use ($start) {
                                 $q3->whereNull('end_date')
                                    ->whereRaw("datetime(start_date, '+1 hour') > ?", [$start->toDateTimeString()]);
                             });
                      });
                });

            // Exclude the period being edited from conflict check
            if ($this->editingId) {
                $conflictQuery->where('id', '!=', $this->editingId);
            }

            $conflict = $conflictQuery->first();

            if ($conflict) {
                $this->addError('startDate', 'Conflict: task "' . $conflict->title . '" on ' . Carbon::parse($conflict->start_date)->format('d M Y, H:i'));
                return;
            }
        }

        if ($this->editingId) {
            $period = UnavailabilityPeriod::findOrFail($this->editingId);

            if (! $isAdmin && $period->user_id !== Auth::id()) {
                return;
            }

            $period->update([
                'start_date'  => $start,
                'end_date'    => $end,
                'description' => $this->description ?: null,
            ]);

            $this->dispatch('toast', message: 'Unavailability period updated.', type: 'success');
        } else {
            UnavailabilityPeriod::create([
                'user_id'     => Auth::id(),
                'start_date'  => $start,
                'end_date'    => $end,
                'description' => $this->description ?: null,
            ]);

            $this->dispatch('toast', message: 'Unavailability period added successfully.', type: 'success');
        }

        $this->closeForm();
    }

    // ─── Delete ───────────────────────────────────────────────

    public function confirmDelete(int $id): void
    {
        $period = UnavailabilityPeriod::find($id);

        if (! $period) {
            return;
        }

        // Admins can delete any period; others only their own future ones
        if (! $this->isAdmin()) {
            if ($period->user_id !== Auth::id()) {
                return;
            }
            if ($period->start_date->isPast()) {
                $this->dispatch('toast', message: 'You can only delete future unavailability periods.', type: 'error');
                return;
            }
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

        if ($period) {
            if ($this->isAdmin() || ($period->user_id === Auth::id() && ! $period->start_date->isPast())) {
                $period->delete();
                $this->dispatch('toast', message: 'Unavailability period deleted.', type: 'success');
            }
        }

        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    // ─── Render ───────────────────────────────────────────────

    public function render()
    {
        $isAdmin = $this->isAdmin();

        // Users for filter buttons (admin only: Staff, Chef, Admin roles)
        $filterUsers = collect();
        if ($isAdmin) {
            $filterUsers = User::with('role')
                ->whereHas('role', fn ($q) => $q->whereIn('name', ['Staff', 'Chef', 'Admin']))
                ->orderBy('name')
                ->get();
        }

        // Build the query
        $query = UnavailabilityPeriod::with('user.role')->orderBy('start_date', 'asc');

        if ($isAdmin) {
            if ($this->myOnly) {
                $query->where('user_id', Auth::id());
            } elseif ($this->filterUserId) {
                $query->where('user_id', $this->filterUserId);
            }
            // else: all users — no filter
        } else {
            // Non-admins see only their own
            $query->where('user_id', Auth::id());
        }

        $periods = $query->get();

        $upcoming = $periods->filter(fn ($p) => $p->end_date->isFuture() || $p->start_date->isFuture());
        $past     = $periods->filter(fn ($p) => $p->end_date->isPast());

        return view('livewire.unavailability', [
            'upcoming'    => $upcoming,
            'past'        => $past,
            'isAdmin'     => $isAdmin,
            'filterUsers' => $filterUsers,
        ]);
    }
}
