<?php

namespace App\Livewire\Meals;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class MealSchedule extends Component
{
    public function render()
    {
        return view('livewire.meals.meal-schedule');
    }
}
