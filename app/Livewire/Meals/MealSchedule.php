<?php

namespace App\Livewire\Meals;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class MealSchedule extends Component
{
    // Render the meal-schedule page.
    public function render()
    {
        return view('livewire.meals.meal-schedule');
    }
}
