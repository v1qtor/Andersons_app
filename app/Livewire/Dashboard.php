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

    public function render()
    {
        $user = auth()->user();
        
        $todayTasks = $user->tasks()
            ->with(['users', 'taskCategory', 'taskPriority', 'locations'])
            ->whereDate('date', today())
            ->orderBy('start_date')
            ->get();
            
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
            'tasksCount' => $todayTasks->count(),
            'totalTripsCount' => $totalUpcomingTrips,
            'totalDinnerCount' => $totalDinnerPlans,
        ])->layout('components.layouts.app');
    }
}
