<?php

namespace App\Livewire\Meals;

use App\Models\PlannedMeal;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

/*
| MealList: class half of a reusable nested Livewire component
| (paired with resources/views/livewire/meals/meal-list.blade.php).
| Renders the paginated list of planned meals; embeddable in any view
| via <livewire:meals.meal-list />.
|
| Roles:
|   - Admin / Chef ("managers"): see all meals, can delete / edit
|     invitees / view attendee dietary info. Chef can also toggle
|     the "prepared" flag.
|   - Others: only see meals they are invited to and confirm / unconfirm
|     their own participation.
|
| Models: PlannedMeal (belongsTo Meal, belongsToMany User as `subscribers`
| via the `meal_subscriptions` join table that carries `confirmed`,
| hasMany MealGuest); User is also queried for the dietary banner.
*/
#[Layout('components.layouts.app')]
class MealList extends Component
{
    use WithPagination;

    // Holds what the current user is allowed to do on this page.
    private ?array $capabilities = null;

    // Refreshes the list when AddMealModal reports a new meal was saved.
    #[On('mealCreated')]
    public function refreshList(): void
    {
        //
    }

    // Refreshes the list when EditInviteesModal reports attendees changed.
    #[On('mealUpdated')]
    public function refreshAfterUpdate(): void
    {
        //
    }

    /*
     * Deletes a planned meal. Manager-only; checked server-side because
     * hiding the button alone wouldn't stop a forged request.
     */
    public function deleteMeal(int $mealId): void
    {
        if (! $this->capabilities()['canManage']) {
            abort(403, __('Unauthorized.'));
        }

        PlannedMeal::find($mealId)?->delete();
    }

    /*
     * Confirms or unconfirms the current user's attendance on a meal.
     * If they were never invited, the method exits without making any
     * changes, so an uninvited user cannot add themselves this way.
     */
    public function toggleParticipation(int $mealId): void
    {
        // Chefs don't RSVP, they cook.
        if (! $this->capabilities()['canParticipate']) {
            return;
        }

        $meal = PlannedMeal::find($mealId);

        if (! $meal) {
            return;
        }

        // Look up the current user's existing invitation, if any.
        $subscription = $meal->subscribers()->wherePivot('user_id', auth()->id())->first();

        if (! $subscription) {
            // Not invited, nothing to confirm (security guard).
            return;
        }

        // Flip the confirmed flag on their existing invitation.
        $meal->subscribers()->updateExistingPivot(auth()->id(), [
            'confirmed' => ! $subscription->pivot->confirmed,
        ]);
    }

    /*
     * Marks a meal as prepared or removes the mark (Chef-only).
     */
    public function togglePrepared(int $mealId): void
    {
        if (! $this->capabilities()['canTogglePrepared']) {
            abort(403, __('Unauthorized.'));
        }

        $meal = PlannedMeal::findOrFail($mealId);
        $meal->update(['is_prepared' => ! $meal->is_prepared]);
    }

    /*
     * Returns what the current user can do on this page: manage meals,
     * toggle the prepared mark, and/or confirm their own participation.
     */
    private function capabilities(): array
    {
        if ($this->capabilities !== null) {
            return $this->capabilities;
        }

        $role = auth()->user()->role?->name;

        return $this->capabilities = [
            // Can delete meals, see all planned meals, and see the full attendee list + dietary info
            'canManage' => in_array($role, ['Admin', 'Chef']),
            // Can mark/unmark a meal as prepared (Chef only)
            'canTogglePrepared' => $role === 'Chef',
            // Can confirm their own participation (everyone except Chef)
            'canParticipate' => $role !== 'Chef',
        ];
    }

    /*
     * Builds the data the view needs: a paginated list of meals (all of
     * them for managers, only the user's invites for everyone else) and
     * the list of household members with dietary info for the banner.
     */
    public function render()
    {
        $capabilities = $this->capabilities();

        // Load each meal together with its dish, attendees and guests.
        $meals = PlannedMeal::with([
            'meal',
            'subscribers.role',
            'subscribers.allergies',
            'subscribers.preferences',
            'guests.invitedBy',
        ])
            ->when(! $capabilities['canManage'], function ($query) {
                // Non-managers only see meals they were invited to.
                $query->whereHas('subscribers', fn ($q) => $q->where('user_id', auth()->id()));
            })
            ->orderBy('date_time', 'desc')
            ->paginate(5, ['*'], 'mealsPage');

        // For each meal, prepare the lists the view needs to display:
        // who is still just invited, who has accepted, guest notes,
        // combined dietary info, and the current user's own invitation.
        $authId = auth()->id();
        $meals->getCollection()->each(function ($meal) use ($authId) {
            $meal->invitedSubscribers = $meal->subscribers->where('pivot.confirmed', false)->values();
            $meal->acceptedSubscribers = $meal->subscribers->where('pivot.confirmed', true)->values();
            $meal->guestsWithNotes = $meal->guests->filter(fn ($g) => filled($g->note))->values();
            $meal->subscriberAllergies = $meal->subscribers->flatMap(fn ($s) => $s->allergies->pluck('name'))->unique()->values();
            $meal->subscriberPreferences = $meal->subscribers->flatMap(fn ($s) => $s->preferences->pluck('name'))->unique()->values();
            $meal->mySubscription = $meal->subscribers->firstWhere('id', $authId);
        });

        // Household members (excluding the Chef) who actually have
        // allergies or preferences, used in the dietary banner shown
        // above the meal list for managers.
        $users = User::with(['allergies', 'preferences', 'role'])
            ->whereHas('role', fn ($q) => $q->where('name', '!=', 'Chef'))
            ->where(function ($query) {
                $query->whereHas('allergies')
                    ->orWhereHas('preferences');
            })
            ->get();

        // Pass the meal list, the dietary banner data, and the
        // capability flags to the view.
        return view('livewire.meals.meal-list', [
            'meals' => $meals,
            'dietaryUsers' => $users,
            ...$capabilities,
        ]);
    }
}
