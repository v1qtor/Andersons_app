<?php

namespace App\Livewire\Meals;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class MealPlanning extends Component
{
    // Render the meal-planning page (composes the meal list and add-meal modal child components).
    public function render()
    {
        return view('livewire.meals.meal-planning');
    }
}
