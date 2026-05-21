<?php

namespace App\Livewire\Meals;

use App\Models\MealGuest;
use App\Models\PlannedMeal;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class UpcomingDinners extends Component
{
    use WithPagination;

    public ?int $editingGuestForMealId = null;

    #[Validate([
        'guests.*.name' => 'required|string|max:100',
        'guests.*.note' => 'nullable|string|max:500',
    ], as: [
        'guests.*.name' => 'guest name',
        'guests.*.note' => 'guest note',
    ])]
    public array $guests = [];

    // Confirm the current user's participation in a dinner (attaching them if not yet a subscriber).
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

    // Mark a meal as prepared / unmark it (Chef only).
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

    // Withdraw the current user's confirmation (keeps the subscription row, flips confirmed to false).
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

    // Open the guest editor and prefill it with this user's existing guests (or one blank row).
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

    // Add a blank guest row to the editor.
    public function addGuestRow(): void
    {
        $this->guests[] = ['id' => null, 'name' => '', 'note' => ''];
    }

    // Remove a guest row from the editor (does not persist).
    public function removeGuestRow(int $index): void
    {
        array_splice($this->guests, $index, 1);

        if ($this->guests === []) {
            $this->guests = [['id' => null, 'name' => '', 'note' => '']];
        }
    }

    // Persist all guest rows: upsert existing, create new, delete any the user removed.
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

        $keptIds = [];

        foreach ($this->guests as $row) {
            $name = trim($row['name']);
            $note = filled($row['note']) ? trim($row['note']) : null;

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

            $created = MealGuest::create([
                'planned_meal_id'    => $plannedMeal->id,
                'invited_by_user_id' => $user->id,
                'name'               => $name,
                'note'               => $note,
            ]);
            $keptIds[] = $created->id;
        }

        // Remove any of the user's guests for this meal that were dropped from the editor.
        MealGuest::where('planned_meal_id', $plannedMeal->id)
            ->where('invited_by_user_id', $user->id)
            ->whereNotIn('id', $keptIds)
            ->delete();

        $this->editingGuestForMealId = null;
        $this->guests = [];
        $this->dispatch('toast', message: 'Guests saved successfully.', type: 'success');
    }

    // Delete a single persisted guest by id.
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

    // Close the editor without saving.
    public function cancelGuestEdit(): void
    {
        $this->editingGuestForMealId = null;
        $this->guests = [];
        $this->resetErrorBag();
    }

    // Load today and future dinners, precompute per-row state for the current user, and render the widget.
    public function render()
    {
        $user = auth()->user();
        $isChef = $user->role?->name === 'Chef';

        $dinnerPlans = PlannedMeal::with(['meal', 'subscribers', 'guests'])
            ->whereDate('date_time', '>=', today())
            ->orderBy('date_time')
            ->paginate(3, ['*'], 'dinnerPage');

        // Precompute per-dinner state for the current user so the view stays free of PHP logic.
        // Chef cooks the meals and does not subscribe — only attendee state is per-row work.
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
