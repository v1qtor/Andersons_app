<?php

namespace App\Livewire\Meals;

use Livewire\Attributes\Layout;
use Livewire\Component;

/*
| MealSchedule: page-level Livewire component (acts as the controller
| returning the page). Routed at /meals for every non-manager role.
| Composes only MealList, which scopes itself to the user's invites
| and shows the "Confirm participation" toggle.
*/
#[Layout('components.layouts.app')]
class MealSchedule extends Component
{
    // Renders the page shell; all real logic lives in MealList.
    public function render()
    {
        return view('livewire.meals.meal-schedule');
    }
}
