<?php

namespace App\Livewire;

use App\Models\PlannedMeal;
use App\Models\Task;
use App\Models\Trip;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Component;

class Schedule extends Component
{
    public string $view = 'month'; // day, week, month

    public int $year;
    public int $month;
    public int $day;

    public array $selectedPeople = [];

    public ?string $selectedDay = null;

    public function mount(): void
    {
        $today = Carbon::today();
        $this->year = $today->year;
        $this->month = $today->month;
        $this->day = $today->day;
    }

    public function setView(string $view): void
    {
        $this->view = $view;
    }

    public function previousPeriod(): void
    {
        $date = Carbon::create($this->year, $this->month, $this->day);

        match ($this->view) {
            'day' => $date->subDay(),
            'week' => $date->subWeek(),
            'month' => $date->subMonth(),
        };

        $this->year = $date->year;
        $this->month = $date->month;
        $this->day = $date->day;
        $this->selectedDay = null;
    }

    public function nextPeriod(): void
    {
        $date = Carbon::create($this->year, $this->month, $this->day);

        match ($this->view) {
            'day' => $date->addDay(),
            'week' => $date->addWeek(),
            'month' => $date->addMonth(),
        };

        $this->year = $date->year;
        $this->month = $date->month;
        $this->day = $date->day;
        $this->selectedDay = null;
    }

    public function goToToday(): void
    {
        $today = Carbon::today();
        $this->year = $today->year;
        $this->month = $today->month;
        $this->day = $today->day;
        $this->selectedDay = null;
    }

    public function togglePerson(int $userId): void
    {
        if (in_array($userId, $this->selectedPeople)) {
            $this->selectedPeople = array_values(array_diff($this->selectedPeople, [$userId]));
        } else {
            $this->selectedPeople[] = $userId;
        }
    }

    public function clearFilters(): void
    {
        $this->selectedPeople = [];
    }

    public function openDay(string $date): void
    {
        $this->selectedDay = $date;
    }

    public function closeDay(): void
    {
        $this->selectedDay = null;
    }

    /**
     * Get the date range for the current view.
     */
    private function getDateRange(): array
    {
        return match ($this->view) {
            'day' => [
                Carbon::create($this->year, $this->month, $this->day)->startOfDay(),
                Carbon::create($this->year, $this->month, $this->day)->endOfDay(),
            ],
            'week' => [
                Carbon::create($this->year, $this->month, $this->day)->startOfWeek(Carbon::MONDAY),
                Carbon::create($this->year, $this->month, $this->day)->endOfWeek(Carbon::SUNDAY),
            ],
            'month' => [
                Carbon::create($this->year, $this->month, 1)->startOfDay(),
                Carbon::create($this->year, $this->month, 1)->endOfMonth()->endOfDay(),
            ],
        };
    }

    /**
     * Get the heading string for the current view.
     */
    public function getPeriodLabelProperty(): string
    {
        return match ($this->view) {
            'day' => Carbon::create($this->year, $this->month, $this->day)->format('l, j F Y'),
            'week' => (function () {
                $start = Carbon::create($this->year, $this->month, $this->day)->startOfWeek(Carbon::MONDAY);
                $end = $start->copy()->endOfWeek(Carbon::SUNDAY);

                return $start->format('j M') . ' – ' . $end->format('j M Y');
            })(),
            'month' => Carbon::create($this->year, $this->month, 1)->format('F Y'),
        };
    }

    private function getFilteredTasks(Carbon $start, Carbon $end)
    {
        $query = Task::with(['users', 'locations', 'taskCategory', 'taskPriority'])
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('date', [$start, $end])
                    ->orWhere(function ($q2) use ($start, $end) {
                        $q2->where('startDate', '<=', $end)
                            ->where('endDate', '>=', $start);
                    });
            });

        if (! empty($this->selectedPeople)) {
            $query->whereHas('users', function ($q) {
                $q->whereIn('users.userId', $this->selectedPeople);
            });
        }

        return $query->orderBy('date')->get();
    }

    private function getFilteredMeals(Carbon $start, Carbon $end)
    {
        $query = PlannedMeal::with(['meal', 'subscribers'])
            ->whereBetween('dateTime', [$start, $end]);

        if (! empty($this->selectedPeople)) {
            $query->whereHas('subscribers', function ($q) {
                $q->whereIn('users.userId', $this->selectedPeople);
            });
        }

        return $query->orderBy('dateTime')->get();
    }

    private function getFilteredTrips(Carbon $start, Carbon $end)
    {
        $query = Trip::with(['users', 'tripCategory', 'status'])
            ->where('startDate', '<=', $end)
            ->where('endDate', '>=', $start);

        if (! empty($this->selectedPeople)) {
            $query->whereHas('users', function ($q) {
                $q->whereIn('users.userId', $this->selectedPeople);
            });
        }

        return $query->orderBy('startDate')->get();
    }

    /**
     * Group events by date string (Y-m-d) for calendar rendering.
     */
    private function groupEventsByDate($tasks, $meals, $trips, Carbon $start, Carbon $end): array
    {
        $events = [];

        foreach ($tasks as $task) {
            $dateKey = $task->date ? $task->date->format('Y-m-d') : $task->startDate->format('Y-m-d');
            $events[$dateKey]['tasks'][] = $task;
        }

        foreach ($meals as $meal) {
            $dateKey = $meal->dateTime->format('Y-m-d');
            $events[$dateKey]['meals'][] = $meal;
        }

        foreach ($trips as $trip) {
            $current = $trip->startDate->copy()->max($start);
            $tripEnd = $trip->endDate->copy()->min($end);

            while ($current->lte($tripEnd)) {
                $dateKey = $current->format('Y-m-d');
                $events[$dateKey]['trips'][] = $trip;
                $current->addDay();
            }
        }

        return $events;
    }

    public function render()
    {
        [$start, $end] = $this->getDateRange();

        $tasks = $this->getFilteredTasks($start, $end);
        $meals = $this->getFilteredMeals($start, $end);
        $trips = $this->getFilteredTrips($start, $end);

        $eventsByDate = $this->groupEventsByDate($tasks, $meals, $trips, $start, $end);

        $users = User::with('role')->get();

        // Build calendar grid for month view
        $calendarDays = [];
        if ($this->view === 'month') {
            $firstOfMonth = Carbon::create($this->year, $this->month, 1);
            // Monday = 1, Sunday = 7. We want Monday first.
            $startDow = $firstOfMonth->dayOfWeekIso; // 1=Mon..7=Sun
            $daysInMonth = $firstOfMonth->daysInMonth;

            // Padding for before the 1st
            for ($i = 1; $i < $startDow; $i++) {
                $calendarDays[] = null;
            }
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $calendarDays[] = $d;
            }
        }

        // Build week days for week view
        $weekDays = [];
        if ($this->view === 'week') {
            $weekStart = Carbon::create($this->year, $this->month, $this->day)->startOfWeek(Carbon::MONDAY);
            for ($i = 0; $i < 7; $i++) {
                $weekDays[] = $weekStart->copy()->addDays($i);
            }
        }

        // Day details
        $dayDetails = null;
        if ($this->selectedDay) {
            $dayDate = Carbon::parse($this->selectedDay);
            $dayDetails = [
                'tasks' => $eventsByDate[$this->selectedDay]['tasks'] ?? [],
                'meals' => $eventsByDate[$this->selectedDay]['meals'] ?? [],
                'trips' => $eventsByDate[$this->selectedDay]['trips'] ?? [],
            ];
        }

        return view('livewire.schedule', [
            'tasks' => $tasks,
            'meals' => $meals,
            'trips' => $trips,
            'eventsByDate' => $eventsByDate,
            'users' => $users,
            'calendarDays' => $calendarDays,
            'weekDays' => $weekDays,
            'dayDetails' => $dayDetails,
            'today' => Carbon::today()->format('Y-m-d'),
        ]);
    }
}

