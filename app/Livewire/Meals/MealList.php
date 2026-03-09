<?php

namespace App\Livewire\Meals;

use App\Models\PlannedMeal;
use App\Models\User;
use Livewire\Component;

class MealList extends Component
{
    public bool $showDeleteConfirm = false;
    public ?int $deletingMealId = null;

    protected $listeners = ['mealCreated' => '$refresh'];

    public function confirmDelete(int $mealId): void
    {
        $this->deletingMealId = $mealId;
        $this->showDeleteConfirm = true;
    }

    public function deleteMeal(): void
    {
        if ($this->deletingMealId) {
            PlannedMeal::find($this->deletingMealId)?->delete();
        }

        $this->cancelDelete();
    }

    public function cancelDelete(): void
    {
        $this->showDeleteConfirm = false;
        $this->deletingMealId = null;
    }

    public function render()
    {
        $meals = PlannedMeal::with([
            'meal',
            'subscribers.role',
            'subscribers.allergies',
            'subscribers.preferences',
        ])->orderBy('date_time', 'desc')->get();

        $users = User::with(['allergies', 'preferences', 'role'])
            ->where(function ($query) {
                $query->whereHas('allergies')
                    ->orWhereHas('preferences');
            })
            ->get();

        return view('livewire.meals.meal-list', [
            'meals' => $meals,
            'dietaryUsers' => $users,
        ]);
    }
}
