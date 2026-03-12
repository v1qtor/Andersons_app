<?php

namespace App\Livewire\Schedule;

use App\Models\PlannedMeal;
use App\Models\Task;
use App\Models\TaskCategory;
use App\Models\TaskPriority;
use App\Models\Trip;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ScheduleCalendar extends Component
{
    public string $view = 'month'; // day, week, month

    public int $year;
    public int $month;
    public int $day;

    public array $selectedPeople = [];

    public bool $showMyTasksOnly = false;

    public ?string $selectedDay = null;

    // ─── Task CRUD ────────────────────────────────────────────
    public bool $showTaskModal = false;
    public ?int $editingTaskId = null;
    public string $title = '';
    public string $description = '';
    public string $startDate = '';
    public ?string $endDate = '';
    public ?int $taskCategoryId = null;
    public ?int $taskPriorityId = null;
    public bool $isComplete = false;
    public ?int $taskOwnerId = null;
    public array $assignedUserIds = [];

    public bool $showDeleteModal = false;
    public ?int $deletingTaskId = null;

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
    }

    public function openDay(string $date): void
    {
        $this->selectedDay = $date;
    }

    public function closeDay(): void
    {
        $this->selectedDay = null;
    }

    // ─── Task CRUD Methods ────────────────────────────────────

    public function setTaskOwner(int $userId): void
    {
        if (! $this->isAdmin()) {
            return;
        }
        $this->taskOwnerId = $userId;
    }

    public function toggleAssignedUser(int $userId): void
    {
        if (in_array($userId, $this->assignedUserIds)) {
            $this->assignedUserIds = array_values(array_diff($this->assignedUserIds, [$userId]));
        } else {
            $this->assignedUserIds[] = $userId;
        }
    }

    public function openCreateModal(?string $date = null): void
    {
        $this->resetForm();
        $this->taskOwnerId = Auth::id();
        $this->assignedUserIds = [Auth::id()];
        if ($date) {
            $this->startDate = $date . 'T09:00';
            $this->endDate = $date . 'T10:00';
        }
        $this->showTaskModal = true;
    }

    public function openEditModal(int $taskId): void
    {
        $task = Task::findOrFail($taskId);

        if (! $this->canManageTask($task)) {
            return;
        }

        $this->editingTaskId = $task->id;
        $this->title = $task->title;
        $this->description = $task->description ?? '';
        $this->startDate = $task->start_date->format('Y-m-d\TH:i');
        $this->endDate = $task->end_date ? $task->end_date->format('Y-m-d\TH:i') : '';
        $this->taskCategoryId = $task->task_category_id;
        $this->taskPriorityId = $task->task_priority_id;
        $this->isComplete = $task->is_complete;
        $this->taskOwnerId = $task->users()->wherePivot('is_owner', true)->value('users.id');
        $this->assignedUserIds = $task->users->pluck('id')->toArray();
        $this->showTaskModal = true;
    }

    public function saveTask(): void
    {
        // Normalize null/empty endDate to empty string for consistent handling
        $endDateValue = ($this->endDate !== null && $this->endDate !== '') ? $this->endDate : null;

        // Prepare data treating empty strings as null
        $validationData = [
            'title' => $this->title,
            'description' => $this->description !== '' ? $this->description : null,
            'startDate' => $this->startDate,
            'endDate' => $endDateValue,
            'taskCategoryId' => $this->taskCategoryId,
            'taskPriorityId' => $this->taskPriorityId,
        ];

        $validated = \Illuminate\Support\Facades\Validator::make($validationData, [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'startDate' => 'required|date',
            'endDate' => 'nullable|date|after:startDate',
            'taskCategoryId' => 'required|integer|exists:task_categories,id',
            'taskPriorityId' => 'nullable|integer|exists:task_priorities,id',
        ], [], [
            'taskCategoryId' => __('category'),
            'taskPriorityId' => __('priority'),
            'startDate' => __('start date'),
            'endDate' => __('end date'),
        ])->validate();

        $startDt = Carbon::parse($validated['startDate']);
        $endDt = $validated['endDate'] ? Carbon::parse($validated['endDate']) : null;

        $data = [
            'title' => $validated['title'],
            'description' => $validated['description'],
            'start_date' => $startDt,
            'end_date' => $endDt,
            'date' => $startDt,
            'task_category_id' => $validated['taskCategoryId'],
            'task_priority_id' => $validated['taskPriorityId'],
            'is_complete' => $this->editingTaskId ? $this->isComplete : false,
        ];

        if ($this->editingTaskId) {
            $task = Task::findOrFail($this->editingTaskId);
            if (! $this->canManageTask($task)) {
                return;
            }
            $task->update($data);

            // Only admins can change user assignments
            if ($this->isAdmin()) {
                $ownerId = $this->taskOwnerId
                    ?: $task->users()->wherePivot('is_owner', true)->value('users.id');

                $syncData = [];

                // Owner is always included
                if ($ownerId) {
                    $syncData[$ownerId] = ['is_owner' => true];
                }

                // Assigned users (non-owners)
                foreach ($this->assignedUserIds as $uid) {
                    if (! isset($syncData[$uid])) {
                        $syncData[$uid] = ['is_owner' => false];
                    }
                }

                $task->users()->sync($syncData);
            }
        } else {
            $task = Task::create($data);

            // Non-admin: creator is always the sole owner
            // Admin: can pick a different owner and assign multiple users
            $ownerId = ($this->isAdmin() && $this->taskOwnerId)
                ? $this->taskOwnerId
                : Auth::id();

            $syncData = [];
            $syncData[$ownerId] = ['is_owner' => true];

            // Admin can assign additional users
            if ($this->isAdmin()) {
                foreach ($this->assignedUserIds as $uid) {
                    if (! isset($syncData[$uid])) {
                        $syncData[$uid] = ['is_owner' => false];
                    }
                }
            }

            $task->users()->sync($syncData);
        }

        $this->showTaskModal = false;
        $this->resetForm();
    }

    public function confirmDelete(int $taskId): void
    {
        $this->deletingTaskId = $taskId;
        $this->showDeleteModal = true;
    }

    public function deleteTask(): void
    {
        if (! $this->deletingTaskId) {
            return;
        }

        $task = Task::findOrFail($this->deletingTaskId);

        if (! $this->canManageTask($task)) {
            return;
        }

        $task->users()->detach();
        $task->locations()->detach();
        $task->delete();

        $this->showDeleteModal = false;
        $this->deletingTaskId = null;
    }

    public function markComplete(int $taskId): void
    {
        $task = Task::findOrFail($taskId);

        // Any user assigned to the task (or admin) can mark it complete
        $isAssigned = $task->users()->where('users.id', Auth::id())->exists();
        if (! $isAssigned && ! $this->isAdmin()) {
            return;
        }

        $task->update(['is_complete' => true]);
    }

    private function resetForm(): void
    {
        $this->editingTaskId = null;
        $this->title = '';
        $this->description = '';
        $this->startDate = '';
        $this->endDate = '';
        $this->taskCategoryId = null;
        $this->taskPriorityId = null;
        $this->isComplete = false;
        $this->taskOwnerId = null;
        $this->assignedUserIds = [];
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
        }

        if (! empty($this->selectedPeople)) {
            $query->whereHas('users', function ($q) {
                $q->whereIn('users.id', $this->selectedPeople);
            });
        }

        return $query->orderBy('date')->get();
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
            'isAdmin' => $this->isAdmin(),
        ]);
    }
}

