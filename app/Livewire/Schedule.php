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


}

