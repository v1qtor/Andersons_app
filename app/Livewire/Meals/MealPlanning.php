<?php

namespace App\Livewire\Meals;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class MealPlanning extends Component
{
    public function render()
    {
        return view('livewire.meals.meal-planning');
    }
}
