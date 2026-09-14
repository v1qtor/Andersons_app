<?php

namespace App\Livewire\Schedule;

use App\Models\Birthdate;
use App\Models\CollaborationRequest;
use App\Models\Location;
use App\Models\PlannedMeal;
use App\Models\Task;
use App\Models\TaskCategory;
use App\Models\TaskPriority;
use App\Models\Trip;
use App\Models\UnavailabilityPeriod;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ScheduleCalendar extends Component
{
    use CollaborationSchedule, CrudSchedule, PrintSchedule;

    public string $view = 'week'; // day, week, month

    public int $year;

    public int $month;

    public int $day;

    public array $selectedPeople = [];

    public bool $showMyTasksOnly = false;

    public string $myTaskOwnershipFilter = 'all'; // 'all', 'owner', 'not-owned'

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
        if ($this->view === 'month') {
            $date = Carbon::create($this->year, $this->month, 1)->subMonth();
        } else {
            $date = Carbon::create($this->year, $this->month, $this->day);
            match ($this->view) {
                'day' => $date->subDay(),
                'week' => $date->subWeek(),
            };
        }

        $this->year = $date->year;
        $this->month = $date->month;
        $this->day = $date->day;
        $this->selectedDay = null;
    }

    public function nextPeriod(): void
    {
        if ($this->view === 'month') {
            $date = Carbon::create($this->year, $this->month, 1)->addMonth();
        } else {
            $date = Carbon::create($this->year, $this->month, $this->day);
            match ($this->view) {
                'day' => $date->addDay(),
                'week' => $date->addWeek(),
            };
        }

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

    public function setMyTasksOnly(bool $value): void
    {
        $this->showMyTasksOnly = $value;
        // Reset ownership filter when switching between My Tasks / All Tasks
        $this->myTaskOwnershipFilter = 'all';
    }

    public function setMyTaskOwnershipFilter(string $filter): void
    {
        $this->myTaskOwnershipFilter = $filter;
    }

    public function openDay(string $date): void
    {
        $this->selectedDay = $date;
    }

    public function closeDay(): void
    {
        $this->selectedDay = null;
    }

    private function isAdmin(): bool
    {
        return Auth::user()->role && Auth::user()->role->name === 'Admin';
    }

    private function canManageTask(Task $task): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $task->users()
            ->where('users.id', Auth::id())
            ->wherePivot('is_owner', true)
            ->exists();
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

                return $start->format('j M').' – '.$end->format('j M Y');
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
                        $q2->where('start_date', '<=', $end)
                            ->where('end_date', '>=', $start);
                    })
                    ->orWhere(function ($q2) use ($start, $end) {
                        $q2->whereNull('end_date')
                            ->whereBetween('start_date', [$start, $end]);
                    });
            });

        if ($this->showMyTasksOnly) {
            $query->whereHas('users', function ($q) {
                $q->where('users.id', Auth::id());
            });

            // Apply ownership sub-filter when My Tasks is active
            if ($this->myTaskOwnershipFilter === 'owner') {
                $query->whereHas('users', function ($q) {
                    $q->where('users.id', Auth::id())
                        ->where('user_tasks.is_owner', true);
                });
            } elseif ($this->myTaskOwnershipFilter === 'not-owned') {
                $query->whereHas('users', function ($q) {
                    $q->where('users.id', Auth::id())
                        ->where('user_tasks.is_owner', false);
                });
            }
        }

        if (! empty($this->selectedPeople)) {
            $query->whereHas('users', function ($q) {
                $q->whereIn('users.id', $this->selectedPeople);
            });
        }

        return $query->orderBy('date')->get();
    }

    /**
     * Get birthdays (from users.birthdate and birthdates table) that fall within the range.
     * Returns array of ['name' => string, 'date' => 'Y-m-d', 'notes' => string|null]
     */
    private function getBirthdays(Carbon $start, Carbon $end): array
    {
        $birthdays = [];

        // Collect all month-day strings within the range
        $current = $start->copy()->startOfDay();
        $rangeDays = [];
        while ($current->lte($end)) {
            $rangeDays[] = $current->format('m-d');
            $current->addDay();
        }

        // Only from birthdates table (includes user-synced entries via is_user = true).
        // Cached briefly: birthdates change rarely, but this runs on every
        // calendar render (navigating periods, toggling filters, etc.).
        $allBirthdates = Cache::remember('birthdates.all', 60, fn () => Birthdate::select('name', 'notes', 'birthdate')->get());

        foreach ($allBirthdates as $entry) {
            $md = $entry->birthdate->format('m-d');
            if (in_array($md, $rangeDays)) {
                foreach ([$start->year, $end->year] as $year) {
                    $date = Carbon::createFromFormat('Y-m-d', $year.'-'.$md);
                    if ($date->between($start, $end)) {
                        $birthdays[] = [
                            'name' => $entry->name,
                            'date' => $date->format('Y-m-d'),
                            'notes' => $entry->notes,
                        ];
                        break;
                    }
                }
            }
        }

        return $birthdays;
    }

    private function getFilteredMeals(Carbon $start, Carbon $end)
    {
        $query = PlannedMeal::with(['meal', 'subscribers'])
            ->whereBetween('date_time', [$start, $end]);

        if ($this->showMyTasksOnly) {
            $query->whereHas('subscribers', function ($q) {
                $q->where('users.id', Auth::id());
            });
        }

        if (! empty($this->selectedPeople)) {
            $query->whereHas('subscribers', function ($q) {
                $q->whereIn('users.id', $this->selectedPeople);
            });
        }

        return $query->orderBy('date_time')->get();
    }

    private function getFilteredTrips(Carbon $start, Carbon $end)
    {
        $query = Trip::with(['users', 'tripCategory', 'status'])
            ->where('start_date', '<=', $end)
            ->where('end_date', '>=', $start);

        if ($this->showMyTasksOnly) {
            $query->whereHas('users', function ($q) {
                $q->where('users.id', Auth::id());
            });
        }

        if (! empty($this->selectedPeople)) {
            $query->whereHas('users', function ($q) {
                $q->whereIn('users.id', $this->selectedPeople);
            });
        }

        return $query->orderBy('start_date')->get();
    }

    /**
     * Group events by date string (Y-m-d) for calendar rendering.
     */
    private function groupEventsByDate($tasks, $meals, $trips, Carbon $start, Carbon $end): array
    {
        $events = [];

        foreach ($tasks as $task) {
            $dateKey = $task->date ? $task->date->format('Y-m-d') : $task->start_date->format('Y-m-d');
            $events[$dateKey]['tasks'][] = $task;
        }

        foreach ($meals as $meal) {
            $dateKey = $meal->date_time->format('Y-m-d');
            $events[$dateKey]['meals'][] = $meal;
        }

        foreach ($trips as $trip) {
            $current = $trip->start_date->copy()->max($start);
            $tripEnd = $trip->end_date->copy()->min($end);

            while ($current->lte($tripEnd)) {
                $dateKey = $current->format('Y-m-d');
                $events[$dateKey]['trips'][] = $trip;
                $current->addDay();
            }
        }

        return $events;
    }

    /**
     * Build structured data for the week view with overlap detection.
     * Each event gets: topPercent, heightPercent, leftPercent, widthPercent
     */
    private function buildWeekViewData(array $weekDays, array $eventsByDate): array
    {
        $data = [];

        foreach ($weekDays as $weekDay) {
            $dateStr = $weekDay->format('Y-m-d');
            $dayEvents = $eventsByDate[$dateStr] ?? [];
            $allDayItems = [];
            $timedItems = [];

            // Trips = all-day
            foreach ($dayEvents['trips'] ?? [] as $trip) {
                $allDayItems[] = [
                    'type' => 'trip',
                    'model' => $trip,
                ];
            }

            // Tasks with start/end times
            foreach ($dayEvents['tasks'] ?? [] as $task) {
                $startTime = $task->start_date;
                $endTime = $task->end_date ?? $startTime->copy()->addHour();
                // Clamp to same day
                $dayStart = $weekDay->copy()->startOfDay();
                $dayEnd = $weekDay->copy()->endOfDay();
                if ($startTime->lt($dayStart)) {
                    $startTime = $dayStart->copy();
                }
                if ($endTime->gt($dayEnd)) {
                    $endTime = $dayEnd->copy();
                }

                $startMinutes = $startTime->hour * 60 + $startTime->minute;
                $endMinutes = $endTime->hour * 60 + $endTime->minute;
                // Minimum 30 min display
                if ($endMinutes - $startMinutes < 30) {
                    $endMinutes = $startMinutes + 30;
                }

                $timedItems[] = [
                    'type' => 'task',
                    'model' => $task,
                    'startMin' => $startMinutes,
                    'endMin' => min($endMinutes, 1440),
                ];
            }

            // Meals (treat as 30-min blocks)
            foreach ($dayEvents['meals'] ?? [] as $meal) {
                $startMinutes = $meal->date_time->hour * 60 + $meal->date_time->minute;
                $endMinutes = $startMinutes + 30;

                $timedItems[] = [
                    'type' => 'meal',
                    'model' => $meal,
                    'startMin' => $startMinutes,
                    'endMin' => min($endMinutes, 1440),
                ];
            }

            // Sort by start time
            usort($timedItems, fn ($a, $b) => $a['startMin'] <=> $b['startMin']);

            // Overlap detection: assign columns
            $positionedItems = $this->assignOverlapColumns($timedItems);

            $data[$dateStr] = [
                'allDay' => $allDayItems,
                'timed' => $positionedItems,
            ];
        }

        return $data;
    }

    /**
     * Assign column positions to overlapping events (Outlook-style side by side).
     */
    private function assignOverlapColumns(array $items): array
    {
        if (empty($items)) {
            return [];
        }

        // Group overlapping events into clusters
        $clusters = [];
        $currentCluster = [$items[0]];

        for ($i = 1; $i < count($items); $i++) {
            $clusterEnd = max(array_column($currentCluster, 'endMin'));
            if ($items[$i]['startMin'] < $clusterEnd) {
                $currentCluster[] = $items[$i];
            } else {
                $clusters[] = $currentCluster;
                $currentCluster = [$items[$i]];
            }
        }
        $clusters[] = $currentCluster;

        $result = [];

        foreach ($clusters as $cluster) {
            $totalCols = count($cluster);
            foreach ($cluster as $colIndex => $item) {
                $item['col'] = $colIndex;
                $item['totalCols'] = $totalCols;
                $item['topPercent'] = round(($item['startMin'] / 1440) * 100, 4);
                $item['heightPercent'] = round((($item['endMin'] - $item['startMin']) / 1440) * 100, 4);
                $item['leftPercent'] = round(($colIndex / $totalCols) * 100, 4);
                $item['widthPercent'] = round((1 / $totalCols) * 100, 4);
                $result[] = $item;
            }
        }

        return $result;
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
            $startDow = $firstOfMonth->dayOfWeekIso;
            $daysInMonth = $firstOfMonth->daysInMonth;

            for ($i = 1; $i < $startDow; $i++) {
                $calendarDays[] = null;
            }
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $calendarDays[] = $d;
            }
        }

        // Build week days for week view
        $weekDays = [];
        $weekViewData = [];
        if ($this->view === 'week') {
            $weekStart = Carbon::create($this->year, $this->month, $this->day)->startOfWeek(Carbon::MONDAY);
            for ($i = 0; $i < 7; $i++) {
                $weekDays[] = $weekStart->copy()->addDays($i);
            }
            $weekViewData = $this->buildWeekViewData($weekDays, $eventsByDate);
        }

        // Day details
        $dayDetails = null;
        if ($this->selectedDay) {
            $dayDetails = [
                'tasks' => $eventsByDate[$this->selectedDay]['tasks'] ?? [],
                'meals' => $eventsByDate[$this->selectedDay]['meals'] ?? [],
                'trips' => $eventsByDate[$this->selectedDay]['trips'] ?? [],
            ];
        }

        // Collaboration requests
        $pendingIncomingRequests = CollaborationRequest::with(['task', 'requester'])
            ->where('target_user_id', Auth::id())
            ->where('status', 'pending')
            ->get();

        // For the edit form: get target_user_ids of pending outgoing requests for the current task
        $pendingOutgoingUserIds = [];
        if ($this->editingTaskId) {
            $pendingOutgoingUserIds = CollaborationRequest::where('task_id', $this->editingTaskId)
                ->where('status', 'pending')
                ->pluck('target_user_id')
                ->toArray();
        }

        // Unavailable users during the selected task date range
        $unavailableUserIds = [];
        if ($this->startDate) {
            $taskStart = Carbon::parse($this->startDate);
            $taskEnd = $this->endDate ? Carbon::parse($this->endDate) : $taskStart->copy()->addHour();
            $unavailableUserIds = UnavailabilityPeriod::where('start_date', '<', $taskEnd)
                ->where('end_date', '>', $taskStart)
                ->pluck('user_id')
                ->unique()
                ->toArray();
        }

        // Birthdays
        $birthdays = $this->getBirthdays($start, $end);

        // Print data
        $printData = $this->showPrintModal ? $this->getPrintData() : null;

        return view('components.schedule-calendar', [
            'tasks' => $tasks,
            'meals' => $meals,
            'trips' => $trips,
            'eventsByDate' => $eventsByDate,
            'users' => $users,
            'allUsers' => $users,
            'calendarDays' => $calendarDays,
            'weekDays' => $weekDays,
            'weekViewData' => $weekViewData,
            'dayDetails' => $dayDetails,
            'today' => Carbon::today()->format('Y-m-d'),
            'taskCategories' => TaskCategory::all(),
            'taskPriorities' => TaskPriority::all(),
            'locations' => Location::all(),
            'isAdmin' => $this->isAdmin(),
            'pendingIncomingRequests' => $pendingIncomingRequests,
            'pendingOutgoingUserIds' => $pendingOutgoingUserIds,
            'unavailableUserIds' => $unavailableUserIds,
            'printData' => $printData,
            'birthdays' => $birthdays,
        ]);
    }
}
