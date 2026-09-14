<?php

namespace App\Livewire\Schedule;

use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

trait PrintSchedule
{
    // ─── Print Properties ─────────────────────────────────────
    public bool $showPrintModal = false;

    public string $printScope = 'allTasks'; // allTasks, myTasks

    public string $printPeriod = 'weekly';  // daily, weekly, monthly, custom

    public string $printCustomStart = '';

    public string $printCustomEnd = '';

    // ─── Print Methods ─────────────────────────────────────────

    #[On('openPrintModal')]
    public function openPrintModal(): void
    {
        $this->showPrintModal = true;
    }

    public function closePrintModal(): void
    {
        $this->showPrintModal = false;
    }

    public function setPrintScope(string $scope): void
    {
        $this->printScope = $scope;
    }

    public function setPrintPeriod(string $period): void
    {
        $this->printPeriod = $period;
    }

    public function getPrintData(): array
    {
        $start = Carbon::create($this->year, $this->month, $this->day);

        if ($this->printPeriod === 'custom') {
            if (! $this->printCustomStart || ! $this->printCustomEnd) {
                return [
                    'tasks' => collect(),
                    'rangeStart' => null,
                    'rangeEnd' => null,
                    'scope' => $this->printScope,
                    'period' => 'custom',
                ];
            }
            $rangeStart = Carbon::parse($this->printCustomStart)->startOfDay();
            $rangeEnd = Carbon::parse($this->printCustomEnd)->endOfDay();
        } else {
            [$rangeStart, $rangeEnd] = match ($this->printPeriod) {
                'daily' => [
                    $start->copy()->startOfDay(),
                    $start->copy()->endOfDay(),
                ],
                'monthly' => [
                    Carbon::create($this->year, $this->month, 1)->startOfDay(),
                    Carbon::create($this->year, $this->month, 1)->endOfMonth()->endOfDay(),
                ],
                default => [ // weekly
                    $start->copy()->startOfWeek(Carbon::MONDAY),
                    $start->copy()->endOfWeek(Carbon::SUNDAY),
                ],
            };
        }

        $query = Task::with(['users', 'locations', 'taskCategory', 'taskPriority'])
            ->where(function ($q) use ($rangeStart, $rangeEnd) {
                $q->whereBetween('date', [$rangeStart, $rangeEnd])
                    ->orWhere(function ($q2) use ($rangeStart, $rangeEnd) {
                        $q2->where('start_date', '<=', $rangeEnd)
                            ->where('end_date', '>=', $rangeStart);
                    })
                    ->orWhere(function ($q2) use ($rangeStart, $rangeEnd) {
                        $q2->whereNull('end_date')
                            ->whereBetween('start_date', [$rangeStart, $rangeEnd]);
                    });
            });

        if ($this->printScope === 'myTasks') {
            $query->whereHas('users', fn ($q) => $q->where('users.id', Auth::id()));
        }

        $tasks = $query->orderBy('start_date')->get();

        return [
            'tasks' => $tasks,
            'rangeStart' => $rangeStart,
            'rangeEnd' => $rangeEnd,
            'scope' => $this->printScope,
            'period' => $this->printPeriod,
        ];
    }
}
