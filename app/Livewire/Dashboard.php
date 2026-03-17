<?php

namespace App\Livewire;

use App\Models\PlannedMeal;
use App\Models\Task;
use App\Models\Trip;
use Illuminate\Support\Carbon;
use Livewire\Component;

class Dashboard extends Component
{
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
            ->take(3)
            ->get();
            
        $dinnerPlans = PlannedMeal::with(['meal', 'subscribers'])
            ->whereDate('date_time', '>=', today())
            ->orderBy('date_time')
            ->first();
            
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
