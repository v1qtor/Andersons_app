<?php

namespace App\Livewire\Meals;

use App\Models\MealGuest;
use App\Models\PlannedMeal;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

/*
| UpcomingDinners: class half of a reusable nested Livewire component
| (paired with resources/views/livewire/meals/upcoming-dinners.blade.php).
| Dashboard widget that lists today's and upcoming dinners. Each card
| lets the user join or cancel the dinner, manage personal guests they
| are bringing, and (for the Chef) toggle the prepared mark.
|
| Roles:
|   - Chef: marks a dinner as prepared or removes the mark.
|   - Others: join / cancel their attendance and add or edit guests
|     they are personally bringing.
|
| Models: PlannedMeal (the scheduled dinner), MealGuest (personal
| guests brought by a household member), User (looks up subscribers
| and the current user).
*/
class UpcomingDinners extends Component
{
    use WithPagination;

    // The dinner id currently being edited in the guest editor,
    // or null when no editor is open.
    public ?int $editingGuestForMealId = null;

    // Rows in the open guest editor (name + optional note per row).
    // Validation rules apply to every row that gets submitted.
    #[Validate([
        'guests.*.name' => 'required|string|max:100',
        'guests.*.note' => 'nullable|string|max:500',
    ], as: [
        'guests.*.name' => 'guest name',
        'guests.*.note' => 'guest note',
    ])]
    public array $guests = [];

    /*
     * Marks the current user as attending a dinner. If they already
     * had an invitation it just flips it to confirmed, otherwise they
     * are added to the attendees list.
     */
    public function joinMeal(int $plannedMealId): void
    {
        $user = auth()->user();
        $plannedMeal = PlannedMeal::find($plannedMealId);

        if (! $plannedMeal) {
            $this->dispatch('toast', message: 'Meal plan not found.', type: 'error');
            return;
        }

        $isSubscribed = $plannedMeal->subscribers()->where('user_id', $user->id)->exists();

        if ($isSubscribed) {
            $plannedMeal->subscribers()->updateExistingPivot($user->id, ['confirmed' => true]);
        } else {
            $plannedMeal->subscribers()->attach($user->id, ['confirmed' => true]);
        }

        $this->dispatch('toast', message: 'You joined this dinner plan.', type: 'success');
    }

    /*
     * Marks a dinner as prepared or removes the mark (Chef-only).
     * Shows a feedback toast for either outcome.
     */
    public function togglePrepared(int $plannedMealId): void
    {
        if (auth()->user()->role?->name !== 'Chef') {
            abort(403, __('Unauthorized.'));
        }

        $plannedMeal = PlannedMeal::find($plannedMealId);

        if (! $plannedMeal) {
            $this->dispatch('toast', message: 'Meal plan not found.', type: 'error');
            return;
        }

        $plannedMeal->update(['is_prepared' => ! $plannedMeal->is_prepared]);

        $this->dispatch(
            'toast',
            message: $plannedMeal->is_prepared ? 'Meal marked as prepared.' : 'Meal marked as not prepared.',
            type: 'success',
        );
    }

    /*
     * Withdraws the current user's confirmation for a dinner. Their
     * invitation stays on file but is marked as unconfirmed.
     */
    public function cancelMeal(int $plannedMealId): void
    {
        $user = auth()->user();
        $plannedMeal = PlannedMeal::find($plannedMealId);

        if (! $plannedMeal) {
            $this->dispatch('toast', message: 'Meal plan not found.', type: 'error');
            return;
        }

        $isSubscribed = $plannedMeal->subscribers()->where('user_id', $user->id)->exists();

        if ($isSubscribed) {
            $plannedMeal->subscribers()->updateExistingPivot($user->id, ['confirmed' => false]);
            $this->dispatch('toast', message: 'You cancelled your dinner participation.', type: 'success');
            return;
        }

        $this->dispatch('toast', message: 'No dinner subscription found to cancel.', type: 'error');
    }

    /*
     * Opens the guest editor for a dinner, prefilled with the user's
     * existing guests for that dinner (or one empty row if they have
     * none yet). The user must have joined first.
     */
    public function startGuestEdit(int $plannedMealId): void
    {
        $plannedMeal = PlannedMeal::with(['subscribers', 'guests'])->find($plannedMealId);

        if (! $plannedMeal) {
            $this->dispatch('toast', message: 'Meal plan not found.', type: 'error');
            return;
        }

        $mySubscription = $plannedMeal->subscribers->firstWhere('id', auth()->id());
        $isJoined = (bool) ($mySubscription?->pivot?->confirmed);

        if (! $isJoined) {
            $this->dispatch('toast', message: 'Join the dinner first before adding a guest.', type: 'error');
            return;
        }

        $existing = $plannedMeal->guests
            ->where('invited_by_user_id', auth()->id())
            ->values()
            ->map(fn ($g) => ['id' => $g->id, 'name' => $g->name, 'note' => (string) $g->note])
            ->all();

        $this->editingGuestForMealId = $plannedMealId;
        $this->guests = $existing ?: [['id' => null, 'name' => '', 'note' => '']];
    }

    // Adds a blank row to the editor so the user can fill in another guest.
    public function addGuestRow(): void
    {
        $this->guests[] = ['id' => null, 'name' => '', 'note' => ''];
    }

    /*
     * Removes a row from the editor (only on the form, nothing is
     * persisted yet). Keeps one empty row visible if the user removed
     * the last one.
     */
    public function removeGuestRow(int $index): void
    {
        array_splice($this->guests, $index, 1);

        if ($this->guests === []) {
            $this->guests = [['id' => null, 'name' => '', 'note' => '']];
        }
    }

    /*
     * Saves the open guest editor: updates rows that already existed,
     * creates new ones, and deletes any of the user's old guests for
     * this dinner that they removed from the editor.
     */
    public function saveGuests(int $plannedMealId): void
    {
        $user = auth()->user();
        $plannedMeal = PlannedMeal::find($plannedMealId);

        if (! $plannedMeal) {
            $this->dispatch('toast', message: 'Meal plan not found.', type: 'error');
            return;
        }

        $isSubscribed = $plannedMeal->subscribers()->where('user_id', $user->id)->exists();

        if (! $isSubscribed) {
            $this->dispatch('toast', message: 'Join the dinner first before adding a guest.', type: 'error');
            return;
        }

        $this->validate();

        // Track the ids of rows we created or kept so we can delete the rest below.
        $keptIds = [];

        foreach ($this->guests as $row) {
            $name = trim($row['name']);
            $note = filled($row['note']) ? trim($row['note']) : null;

            // Row already existed: update it. The where-clauses make sure
            // a user can only edit guests they themselves added.
            if (! empty($row['id'])) {
                $guest = MealGuest::where('id', $row['id'])
                    ->where('planned_meal_id', $plannedMeal->id)
                    ->where('invited_by_user_id', $user->id)
                    ->first();

                if ($guest) {
                    $guest->update(['name' => $name, 'note' => $note]);
                    $keptIds[] = $guest->id;
                }

                continue;
            }

            // New row: create the guest record for the current user.
            $created = MealGuest::create([
                'planned_meal_id'    => $plannedMeal->id,
                'invited_by_user_id' => $user->id,
                'name'               => $name,
                'note'               => $note,
            ]);
            $keptIds[] = $created->id;
        }

        // Anything the user owned for this meal that didn't make it back
        // into the editor was removed by them, so delete it.
        MealGuest::where('planned_meal_id', $plannedMeal->id)
            ->where('invited_by_user_id', $user->id)
            ->whereNotIn('id', $keptIds)
            ->delete();

        // Close the editor and let the user know it worked.
        $this->editingGuestForMealId = null;
        $this->guests = [];
        $this->dispatch('toast', message: 'Guests saved successfully.', type: 'success');
    }

    /*
     * Deletes one of the current user's saved guests by id. The
     * invited_by_user_id check makes sure a user cannot delete
     * somebody else's guest.
     */
    public function removeGuest(int $guestId): void
    {
        $deleted = MealGuest::where('id', $guestId)
            ->where('invited_by_user_id', auth()->id())
            ->delete();

        if ($deleted) {
            $this->dispatch('toast', message: 'Guest removed.', type: 'success');
            return;
        }

        $this->dispatch('toast', message: 'Guest not found.', type: 'error');
    }

    // Closes the guest editor and throws away any unsaved input.
    public function cancelGuestEdit(): void
    {
        $this->editingGuestForMealId = null;
        $this->guests = [];
        $this->resetErrorBag();
    }

    /*
     * Builds the data the view needs: today's and future dinners
     * (paginated), plus per-dinner state for non-Chef users (have they
     * joined, which guests are theirs) so the view can stay free of
     * PHP logic.
     */
    public function render()
    {
        $user = auth()->user();
        $isChef = $user->role?->name === 'Chef';

        $dinnerPlans = PlannedMeal::with(['meal', 'subscribers', 'guests'])
            ->whereDate('date_time', '>=', today())
            ->orderBy('date_time')
            ->paginate(3, ['*'], 'dinnerPage');

        // For each dinner, attach the current user's own state (joined?
        // which guests are theirs?) so the view just reads flags.
        // The Chef cooks the meals and doesn't subscribe, so this step
        // is skipped for them.
        if (! $isChef) {
            $dinnerPlans->getCollection()->each(function ($dinner) use ($user) {
                $sub = $dinner->subscribers->firstWhere('id', $user->id);
                $dinner->mySubscription = $sub;
                $dinner->isJoined       = (bool) ($sub?->pivot?->confirmed);
                $dinner->myGuests       = $dinner->guests->where('invited_by_user_id', $user->id)->values();
                $dinner->hasGuest       = $dinner->myGuests->isNotEmpty();
            });
        }

        return view('livewire.meals.upcoming-dinners', [
            'dinnerPlans' => $dinnerPlans,
            'isChef'      => $isChef,
        ]);
    }
}
