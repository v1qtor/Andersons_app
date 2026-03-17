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
        $task = Task::find($taskId);
        // Ensure user can update this task
        if ($task && $task->users()->where('user_id', auth()->id())->exists()) {
            $task->update(['is_complete' => true]);
        }
    }

    public function render()
    {
        $user = auth()->user();
        
        $tasksQuery = $user->tasks()
            ->with(['users', 'taskCategory', 'taskPriority', 'locations'])
            ->whereDate('date', today());

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
        $tasksCount = $user->tasks()->whereDate('date', today())->count();
            
        $upcomingTrips = $user->trips()
            ->with('checkpoints')
            ->where('start_date', '>=', today())
            ->orderBy('start_date')
            ->paginate(5);
            
        $dinnerPlans = PlannedMeal::with(['meal', 'subscribers'])
            ->whereDate('date_time', '>=', today())
            ->orderBy('date_time')
            ->paginate(3, ['*'], 'dinnerPage');
            
        $totalDinnerPlans = PlannedMeal::whereDate('date_time', '>=', today())->count();
        $totalUpcomingTrips = $user->trips()->where('start_date', '>=', today())->count();

        return view('livewire.dashboard', [
            'todayTasks' => $todayTasks,
            'upcomingTrips' => $upcomingTrips,
            'dinnerPlans' => $dinnerPlans,
            'tasksCount' => $tasksCount,
            'completedCount' => $user->tasks()->whereDate('date', today())->where('is_complete', true)->count(),
            'totalTripsCount' => $totalUpcomingTrips,
            'totalDinnerCount' => $totalDinnerPlans,
            'priorities' => \App\Models\TaskPriority::all(),
        ])->layout('components.layouts.app');
    }
}
