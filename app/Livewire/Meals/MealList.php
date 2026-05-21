<?php

namespace App\Livewire\Meals;

use App\Models\PlannedMeal;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class MealList extends Component
{
    use WithPagination;

    private ?array $capabilities = null;

    // Re-render when a meal is created elsewhere (e.g. AddMealModal).
    #[On('mealCreated')]
    public function refreshList(): void
    {
        //
    }

    // Re-render when invitees are edited (EditInviteesModal).
    #[On('mealUpdated')]
    public function refreshAfterUpdate(): void
    {
        //
    }

    // Delete a planned meal (managers only).
    public function deleteMeal(int $mealId): void
    {
        if (! $this->capabilities()['canManage']) {
            abort(403, __('Unauthorized.'));
        }

        PlannedMeal::find($mealId)?->delete();
    }

    // Flip the current user's confirmed flag on an invitation (only for users who can participate).
    public function toggleParticipation(int $mealId): void
    {
        // Chef schedules meals — participation confirmation is not their concern
        if (! $this->capabilities()['canParticipate']) {
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

    // Mark a meal as prepared / unmark it (Chef only).
    public function togglePrepared(int $mealId): void
    {
        if (! $this->capabilities()['canTogglePrepared']) {
            abort(403, __('Unauthorized.'));
        }

        $meal = PlannedMeal::findOrFail($mealId);
        $meal->update(['is_prepared' => ! $meal->is_prepared]);
    }

    // Lazy-cached capability map — what the authenticated user can do (manage / toggle prepared / participate).
    private function capabilities(): array
    {
        if ($this->capabilities !== null) {
            return $this->capabilities;
        }

        $role = auth()->user()->role?->name;

        return $this->capabilities = [
            // Can delete meals, see all planned meals, and see the full attendee list + dietary info
            'canManage'          => in_array($role, ['Admin', 'Chef']),
            // Can mark/unmark a meal as prepared (Chef only)
            'canTogglePrepared'  => $role === 'Chef',
            // Can confirm their own participation (everyone except Chef)
            'canParticipate'     => $role !== 'Chef',
        ];
    }

    // Load the paginated meal list, attach per-meal subscriber buckets, and render the view.
    public function render()
    {
        $capabilities = $this->capabilities();

        $meals = PlannedMeal::with([
            'meal',
            'subscribers.role',
            'subscribers.allergies',
            'subscribers.preferences',
            'guests.invitedBy',
        ])
        ->when(! $capabilities['canManage'], function ($query) {
            // Non-managers only see meals they have been invited to
            $query->whereHas('subscribers', fn ($q) => $q->where('user_id', auth()->id()));
        })
        ->orderBy('date_time', 'desc')
        ->paginate(5, ['*'], 'mealsPage');

        // Pre-partition subscribers per meal so the view stays free of PHP logic.
        $authId = auth()->id();
        $meals->getCollection()->each(function ($meal) use ($authId) {
            $meal->invitedSubscribers     = $meal->subscribers->where('pivot.confirmed', false)->values();
            $meal->acceptedSubscribers    = $meal->subscribers->where('pivot.confirmed', true)->values();
            $meal->guestsWithNotes        = $meal->guests->filter(fn ($g) => filled($g->note))->values();
            $meal->subscriberAllergies    = $meal->subscribers->flatMap(fn ($s) => $s->allergies->pluck('name'))->unique()->values();
            $meal->subscriberPreferences  = $meal->subscribers->flatMap(fn ($s) => $s->preferences->pluck('name'))->unique()->values();
            $meal->mySubscription         = $meal->subscribers->firstWhere('id', $authId);
        });

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
