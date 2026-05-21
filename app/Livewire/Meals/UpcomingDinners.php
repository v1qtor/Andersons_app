<?php

namespace App\Livewire\Meals;

use App\Models\PlannedMeal;
use Livewire\Component;
use Livewire\WithPagination;

class UpcomingDinners extends Component
{
    use WithPagination;

    public $editingGuestForMealId = null;
    public $guestName = '';
    public $guestNote = '';

    public function joinMeal($plannedMealId)
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

    public function cancelMeal($plannedMealId)
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

    public function startGuestEdit($plannedMealId)
    {
        $plannedMeal = PlannedMeal::with('subscribers')->find($plannedMealId);

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

        $this->editingGuestForMealId = $plannedMealId;
        $this->guestName = (string) ($mySubscription?->pivot?->guest_name ?? '');
        $this->guestNote = (string) ($mySubscription?->pivot?->guest_note ?? '');
    }

    public function saveGuest($plannedMealId)
    {
        $user = auth()->user();
        $plannedMeal = PlannedMeal::find($plannedMealId);

        if (! $plannedMeal) {
            $this->dispatch('toast', message: 'Meal plan not found.', type: 'error');
            return;
        }

        $this->validate([
            'guestName' => 'required|string|max:100',
            'guestNote' => 'nullable|string|max:500',
        ]);

        $isSubscribed = $plannedMeal->subscribers()->where('user_id', $user->id)->exists();

        if (! $isSubscribed) {
            $this->dispatch('toast', message: 'Join the dinner first before adding a guest.', type: 'error');
            return;
        }

        $plannedMeal->subscribers()->updateExistingPivot($user->id, [
            'guest_name' => trim($this->guestName),
            'guest_note' => filled($this->guestNote) ? trim($this->guestNote) : null,
        ]);

        $this->editingGuestForMealId = null;
        $this->guestName = '';
        $this->guestNote = '';
        $this->dispatch('toast', message: 'Guest saved successfully.', type: 'success');
    }

    public function removeGuest($plannedMealId)
    {
        $user = auth()->user();
        $plannedMeal = PlannedMeal::find($plannedMealId);

        if (! $plannedMeal) {
            $this->dispatch('toast', message: 'Meal plan not found.', type: 'error');
            return;
        }

        $isSubscribed = $plannedMeal->subscribers()->where('user_id', $user->id)->exists();

        if (! $isSubscribed) {
            $this->dispatch('toast', message: 'No meal subscription found.', type: 'error');
            return;
        }

        $plannedMeal->subscribers()->updateExistingPivot($user->id, [
            'guest_name' => null,
            'guest_note' => null,
        ]);

        $this->editingGuestForMealId = null;
        $this->guestName = '';
        $this->guestNote = '';
        $this->dispatch('toast', message: 'Guest removed.', type: 'success');
    }

    public function render()
    {
        $dinnerPlans = PlannedMeal::with(['meal', 'subscribers'])
            ->whereDate('date_time', '>=', today())
            ->orderBy('date_time')
            ->paginate(3, ['*'], 'dinnerPage');

        return view('livewire.meals.upcoming-dinners', [
            'dinnerPlans' => $dinnerPlans,
        ]);
    }
}
