<?php

namespace App\Livewire\Meals;

use Livewire\Attributes\Layout;
use Livewire\Component;

/*
| MealPlanning: page-level Livewire component (acts as the controller
| returning the page). Routed at /admin/meals and /chef/meals.
| Composes MealList, AddMealModal and EditInviteesModal; the role-aware
| behaviour lives inside those children.
*/
#[Layout('components.layouts.app')]
class MealPlanning extends Component
{
    // Renders the page shell; all real logic lives in the child components.
    public function render()
    {
        return view('livewire.meals.meal-planning');
    }
}
