<?php

namespace App\Livewire;

use App\Models\PlannedMeal;
use App\Models\Task;
use App\Models\Trip;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class Dashboard extends Component
{
    use WithPagination;

    public $priorityFilter = '';
    public $timeFilter = '';

    public function markTaskAsDone($taskId)
    {
        $user = auth()->user();
        if (in_array($user->role?->name, ['Family Member', 'The Andersons'])) {
            return; // Restricted roles cannot mark tasks as done
        }

        $task = Task::find($taskId);
        // Ensure user can update this task
        if ($task && $task->users()->where('user_id', $user->id)->exists()) {
            $task->update(['is_complete' => true]);
        }
    }

    public function render()
    {
        $user = auth()->user();
        $isFamilyView = in_array($user->role?->name, ['Family Member', 'The Andersons']);

        if ($isFamilyView) {
            // Family views all tasks but cannot edit
            $tasksQuery = Task::with(['users', 'taskCategory', 'taskPriority', 'locations'])
                ->whereDate('date', today());
            $baseTasksQuery = Task::whereDate('date', today());
        } else {
            // Staff members view only their own assigned tasks and can edit
            $tasksQuery = $user->tasks()
                ->with(['users', 'taskCategory', 'taskPriority', 'locations'])
                ->whereDate('date', today());
            $baseTasksQuery = $user->tasks()->whereDate('date', today());
        }

        if ($this->priorityFilter) {
            $tasksQuery->whereHas('taskPriority', function ($q) {
                $q->where('name', $this->priorityFilter);
            });
        }

        if ($this->timeFilter) {
            if ($this->timeFilter === 'morning') {
                $tasksQuery->whereTime('start_date', '<', '12:00:00');
            } elseif ($this->timeFilter === 'afternoon') {
                $tasksQuery->whereTime('start_date', '>=', '12:00:00')->whereTime('start_date', '<', '17:00:00');
            } elseif ($this->timeFilter === 'evening') {
                $tasksQuery->whereTime('start_date', '>=', '17:00:00');
            }
        }

        $todayTasks = $tasksQuery->orderBy('start_date')->get();
        // Get base count to show how many total regardless of filters
        $tasksCount = (clone $baseTasksQuery)->count();
        $completedCount = (clone $baseTasksQuery)->where('is_complete', true)->count();

        $upcomingTrips = $user->trips()
            ->with('checkpoints')
            ->where('start_date', '>=', today())
            ->orderBy('start_date')
            ->paginate(5);

        $totalDinnerPlans = PlannedMeal::whereDate('date_time', '>=', today())->count();
        $totalUpcomingTrips = $user->trips()->where('start_date', '>=', today())->count();

        return view('livewire.dashboard', [
            'todayTasks' => $todayTasks,
            'upcomingTrips' => $upcomingTrips,
            'tasksCount' => $tasksCount,
            'completedCount' => $completedCount,
            'totalTripsCount' => $totalUpcomingTrips,
            'totalDinnerCount' => $totalDinnerPlans,
            'priorities' => \App\Models\TaskPriority::all(),
            'canManageTasks' => !$isFamilyView,
        ])->layout('components.layouts.app');
    }
}
