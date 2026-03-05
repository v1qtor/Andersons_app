<?php

namespace App\Livewire\PersonalTasks;

use App\Models\Task;
use App\Models\TaskCategory;
use App\Models\TaskPriority;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PersonalTaskCalendar extends Component
{
    public string $view = 'month'; // day, week, month

    public int $year;
    public int $month;
    public int $day;

    public ?string $selectedDay = null;
    public bool $showAllTasks = false; // Admin toggle
    public array $selectedPeople = []; // Not used, required by shared calendar view

    // Create/Edit form
    public bool $showTaskModal = false;
    public ?int $editingTaskId = null;
    public string $title = '';
    public string $description = '';
    public string $startDate = '';
    public string $endDate = '';
    public ?int $taskCategoryId = null;
    public ?int $taskPriorityId = null;

    // Delete confirmation
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

    public function toggleShowAll(): void
    {
        $this->showAllTasks = ! $this->showAllTasks;
    }

    public function openDay(string $date): void
    {
        $this->selectedDay = $date;
    }

    public function closeDay(): void
    {
        $this->selectedDay = null;
    }

    // ─── CRUD ────────────────────────────────────────────────

    public function openCreateModal(?string $date = null): void
    {
        $this->resetForm();
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
        $this->showTaskModal = true;
    }

    public function saveTask(): void
    {
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'startDate' => 'required|date',
            'endDate' => 'nullable|date|after:startDate',
            'taskCategoryId' => 'required|exists:task_categories,id',
            'taskPriorityId' => 'nullable|exists:task_priorities,id',
        ];

        $this->validate($rules);

        $startDt = Carbon::parse($this->startDate);
        $endDt = $this->endDate ? Carbon::parse($this->endDate) : null;

        $data = [
            'title' => $this->title,
            'description' => $this->description ?: null,
            'start_date' => $startDt,
            'end_date' => $endDt,
            'date' => $startDt,
            'task_category_id' => $this->taskCategoryId,
            'task_priority_id' => $this->taskPriorityId,
            'is_complete' => false,
        ];

        if ($this->editingTaskId) {
            $task = Task::findOrFail($this->editingTaskId);
            if (! $this->canManageTask($task)) {
                return;
            }
            $task->update($data);
        } else {
            $task = Task::create($data);
            $task->users()->attach(Auth::id(), ['is_owner' => true]);
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

    public function toggleComplete(int $taskId): void
    {
        $task = Task::findOrFail($taskId);

        if (! $this->canManageTask($task)) {
            return;
        }

        $task->update(['is_complete' => ! $task->is_complete]);
    }

    // ─── Helpers ─────────────────────────────────────────────

    private function resetForm(): void
    {
        $this->editingTaskId = null;
        $this->title = '';
        $this->description = '';
        $this->startDate = '';
        $this->endDate = '';
        $this->taskCategoryId = null;
        $this->taskPriorityId = null;
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

        return $task->users()->where('users.id', Auth::id())->exists();
    }

    public function render()
    {
        [$start, $end] = $this->getDateRange();

        $tasks = $this->getFilteredTasks($start, $end);
        $tasksByDate = $this->groupTasksByDate($tasks);

        // Convert to eventsByDate format (same as ScheduleCalendar)
        $eventsByDate = [];
        foreach ($tasksByDate as $date => $dateTasks) {
            $eventsByDate[$date] = [
                'tasks' => $dateTasks,
                'meals' => [],
                'trips' => [],
            ];
        }

        // Calendar grid for month
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

        // Week days
        $weekDays = [];
        $weekViewData = [];
        if ($this->view === 'week') {
            $weekStart = Carbon::create($this->year, $this->month, $this->day)->startOfWeek(Carbon::MONDAY);
            for ($i = 0; $i < 7; $i++) {
                $weekDays[] = $weekStart->copy()->addDays($i);
            }
            $weekViewData = $this->buildWeekViewData($weekDays, $tasksByDate);
        }

        // Day details (same format as ScheduleCalendar)
        $dayDetails = null;
        if ($this->selectedDay) {
            $dayDetails = [
                'tasks' => $eventsByDate[$this->selectedDay]['tasks'] ?? [],
                'meals' => [],
                'trips' => [],
            ];
        }

        return view('components.schedule-calendar', [
            'mode' => 'personal-tasks',
            'tasks' => $tasks,
            'meals' => collect(),
            'trips' => collect(),
            'eventsByDate' => $eventsByDate,
            'users' => collect(),
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

