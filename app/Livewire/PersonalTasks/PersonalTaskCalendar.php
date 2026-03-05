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


}

