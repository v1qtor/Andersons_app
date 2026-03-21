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
    public $editingGuestForMealId = null;
    public $guestName = '';

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

    public function joinMeal($plannedMealId)
    {
        $user = auth()->user();
        $plannedMeal = PlannedMeal::find($plannedMealId);

        if (! $plannedMeal) {
            $this->dispatch('toast', message: 'Meal plan not found.', type: 'error');
            return;
        }

        $isSubscribed = $plannedMeal->subscribers()->where('user_id', $user->id)->exists();

        if ($isSubscribed) {
            $plannedMeal->subscribers()->updateExistingPivot($user->id, ['confirmed' => true]);
        } else {
            $plannedMeal->subscribers()->attach($user->id, ['confirmed' => true]);
        }

        $this->dispatch('toast', message: 'You joined this dinner plan.', type: 'success');
    }

    public function cancelMeal($plannedMealId)
    {
        $user = auth()->user();
        $plannedMeal = PlannedMeal::find($plannedMealId);

        if (! $plannedMeal) {
            $this->dispatch('toast', message: 'Meal plan not found.', type: 'error');
            return;
        }

        $isSubscribed = $plannedMeal->subscribers()->where('user_id', $user->id)->exists();

        if ($isSubscribed) {
            $plannedMeal->subscribers()->updateExistingPivot($user->id, ['confirmed' => false]);
            $this->dispatch('toast', message: 'You cancelled your dinner participation.', type: 'success');
            return;
        }

        $this->dispatch('toast', message: 'No dinner subscription found to cancel.', type: 'error');
    }

    public function startGuestEdit($plannedMealId)
    {
        $plannedMeal = PlannedMeal::with('subscribers')->find($plannedMealId);

        if (! $plannedMeal) {
            $this->dispatch('toast', message: 'Meal plan not found.', type: 'error');
            return;
        }

        $mySubscription = $plannedMeal->subscribers->firstWhere('id', auth()->id());
        $isJoined = (bool) ($mySubscription?->pivot?->confirmed);

        if (! $isJoined) {
            $this->dispatch('toast', message: 'Join the dinner first before adding a guest.', type: 'error');
            return;
        }

        $this->editingGuestForMealId = $plannedMealId;
        $this->guestName = (string) ($mySubscription?->pivot?->guest_name ?? '');
    }

    public function saveGuest($plannedMealId)
    {
        $user = auth()->user();
        $plannedMeal = PlannedMeal::find($plannedMealId);

        if (! $plannedMeal) {
            $this->dispatch('toast', message: 'Meal plan not found.', type: 'error');
            return;
        }

        $this->validate([
            'guestName' => 'required|string|max:100',
        ]);

        $isSubscribed = $plannedMeal->subscribers()->where('user_id', $user->id)->exists();

        if (! $isSubscribed) {
            $this->dispatch('toast', message: 'Join the dinner first before adding a guest.', type: 'error');
            return;
        }

        $plannedMeal->subscribers()->updateExistingPivot($user->id, [
            'guest_name' => trim($this->guestName),
        ]);

        $this->editingGuestForMealId = null;
        $this->guestName = '';
        $this->dispatch('toast', message: 'Guest saved successfully.', type: 'success');
    }

    public function removeGuest($plannedMealId)
    {
        $user = auth()->user();
        $plannedMeal = PlannedMeal::find($plannedMealId);

        if (! $plannedMeal) {
            $this->dispatch('toast', message: 'Meal plan not found.', type: 'error');
            return;
        }

        $isSubscribed = $plannedMeal->subscribers()->where('user_id', $user->id)->exists();

        if (! $isSubscribed) {
            $this->dispatch('toast', message: 'No meal subscription found.', type: 'error');
            return;
        }

        $plannedMeal->subscribers()->updateExistingPivot($user->id, [
            'guest_name' => null,
        ]);

        $this->editingGuestForMealId = null;
        $this->guestName = '';
        $this->dispatch('toast', message: 'Guest removed.', type: 'success');
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
            'completedCount' => $completedCount,
            'totalTripsCount' => $totalUpcomingTrips,
            'totalDinnerCount' => $totalDinnerPlans,
            'priorities' => \App\Models\TaskPriority::all(),
            'canManageTasks' => !$isFamilyView,
        ])->layout('components.layouts.app');
    }
}
