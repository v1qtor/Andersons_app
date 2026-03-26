<?php

namespace App\Livewire\Meals;

use App\Models\PlannedMeal;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
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

    public function toggleParticipation(int $mealId): void
    {
        // Chef schedules meals — participation confirmation is not their concern
        if (! $this->resolveCapabilities()['canParticipate']) {
            return;
        }

        $meal = PlannedMeal::find($mealId);

        if (! $meal) {
            return;
        }

        $subscription = $meal->subscribers()->wherePivot('user_id', auth()->id())->first();

        if (! $subscription) {
            // User was not invited — do nothing (security guard)
            return;
        }

        $meal->subscribers()->updateExistingPivot(auth()->id(), [
            'confirmed' => ! $subscription->pivot->confirmed,
        ]);
    }

    public function togglePrepared(int $mealId): void
    {
        if (! $this->resolveCapabilities()['canTogglePrepared']) {
            abort(403, __('Unauthorized.'));
        }

        $meal = PlannedMeal::findOrFail($mealId);
        $meal->update(['is_prepared' => ! $meal->is_prepared]);
    }

    /**
     * Derive what the authenticated user is allowed to do in the meal list.
     * Role names are only ever referenced here — never in the view.
     */
    private function resolveCapabilities(): array
    {
        $role = auth()->user()->role?->name;

        return [
            // Can delete meals, see all planned meals, and see the full attendee list + dietary info
            'canManage'          => in_array($role, ['Admin', 'Chef']),
            // Can mark/unmark a meal as prepared (Chef only)
            'canTogglePrepared'  => $role === 'Chef',
            // Can confirm their own participation (everyone except Chef)
            'canParticipate'     => $role !== 'Chef',
        ];
    }

    public function render()
    {
        $capabilities = $this->resolveCapabilities();

        $meals = PlannedMeal::with([
            'meal',
            'subscribers.role',
            'subscribers.allergies',
            'subscribers.preferences',
        ])
        ->when(! $capabilities['canManage'], function ($query) {
            // Non-managers only see meals they have been invited to
            $query->whereHas('subscribers', fn ($q) => $q->where('user_id', auth()->id()));
        })
        ->orderBy('date_time', 'desc')
        ->get();

        $users = User::with(['allergies', 'preferences', 'role'])
            // Chef prepares the meals — their own dietary info is not a concern for planning
            ->whereHas('role', fn ($q) => $q->where('name', '!=', 'Chef'))
            ->where(function ($query) {
                $query->whereHas('allergies')
                    ->orWhereHas('preferences');
            })
            ->get();

        return view('livewire.meals.meal-list', [
            'meals'        => $meals,
            'dietaryUsers' => $users,
            ...$capabilities,
        ]);
    }
}
