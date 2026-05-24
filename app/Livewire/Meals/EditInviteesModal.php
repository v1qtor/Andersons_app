<?php

namespace App\Livewire\Meals;

use App\Events\NotificationCreated;
use App\Models\PlannedMeal;
use App\Models\User;
use App\Models\UserNotification;
use Livewire\Attributes\On;
use Livewire\Component;

/*
| EditInviteesModal: class half of a reusable nested Livewire component
| (paired with resources/views/livewire/meals/edit-invitees-modal.blade.php).
| Owns the "Edit Invitees" popup, opened from the MealList edit button
| for managers (Admin / Chef). Lets them change who is invited to an
| existing meal and notifies anyone newly added.
|
| Models: PlannedMeal (the meal being edited), User (invitee picker
| and notification target), UserNotification (one row per newly
| invited user).
*/
class EditInviteesModal extends Component
{
    // Whether the popup is currently open.
    public bool $showModal = false;

    // The meal currently being edited and the labels shown in the
    // popup header (filled when the popup opens).
    public ?int $mealId = null;
    public string $mealName = '';
    public string $mealDateTime = '';

    // Ids of users currently selected in the picker.
    public array $invitees = [];

    /*
     * Opens the popup for a specific meal when MealList dispatches
     * 'editInvitees'. Prefills the picker with the meal's current
     * attendees so the user sees the starting state.
     */
    #[On('editInvitees')]
    public function openModal(int $mealId): void
    {
        if (! $this->canManage()) {
            return;
        }

        $meal = PlannedMeal::with(['meal', 'subscribers'])->find($mealId);

        if (! $meal) {
            return;
        }

        $this->mealId       = $meal->id;
        $this->mealName     = $meal->meal?->name ?? __('Unnamed Meal');
        $this->mealDateTime = $meal->date_time->translatedFormat('l, d F Y') . ' ' . __('at') . ' ' . $meal->date_time->format('H:i');
        $this->invitees     = $meal->subscribers->pluck('id')->map(fn ($id) => (int) $id)->all();
        $this->showModal    = true;
    }

    // Validation rules: the picker must contain real user ids.
    public function rules(): array
    {
        return [
            'invitees'   => ['array'],
            'invitees.*' => ['exists:users,id'],
        ];
    }

    /*
     * Saves the picker selection: works out who was added and who was
     * removed compared to the meal's current attendees, applies both
     * changes, sends an invitation notification to the newly added,
     * and tells MealList to refresh.
     */
    public function save(): void
    {
        if (! $this->canManage() || ! $this->mealId) {
            return;
        }

        $this->validate();

        $meal = PlannedMeal::with('meal')->find($this->mealId);

        if (! $meal) {
            return;
        }

        // Compare the saved attendees with the freshly chosen ones.
        $current = $meal->subscribers()->pluck('users.id')->map(fn ($id) => (int) $id)->all();
        $staged  = array_map('intval', $this->invitees);

        $toAdd    = array_values(array_diff($staged, $current));
        $toRemove = array_values(array_diff($current, $staged));

        // Drop anyone the manager removed from the picker.
        if (! empty($toRemove)) {
            $meal->subscribers()->detach($toRemove);
        }

        // Add anyone newly picked, starting them off as unconfirmed.
        if (! empty($toAdd)) {
            $meal->subscribers()->attach(
                collect($toAdd)->mapWithKeys(fn ($id) => [$id => ['confirmed' => false]])->all()
            );

            // Send each new invitee a notification on their dashboard
            // (only those who haven't disabled meal notifications).
            foreach ($toAdd as $userId) {
                $invitee = User::find($userId);
                if ($invitee && $this->userHasMealNotificationsEnabled($invitee)) {
                    $notification = UserNotification::create([
                        'user_id'      => $userId,
                        'from_user_id' => auth()->id(),
                        'title'        => 'You\'re Invited to a Meal',
                        'message'      => 'You\'ve been invited to ' . ($meal->meal?->name ?? 'a meal') . ' on ' . $meal->date_time->format('M d, H:i'),
                        'type'         => 'meal_assignment',
                        'action_url'   => '/meals',
                    ]);

                    broadcast(new NotificationCreated($notification));
                }
            }
        }

        // Close the popup and let MealList refresh so the new chips show up.
        $this->showModal = false;
        $this->dispatch('mealUpdated');
    }

    /*
     * Builds the data the view needs: the list of users that can be
     * invited (everyone in the household except the Chef).
     */
    public function render()
    {
        return view('livewire.meals.edit-invitees-modal', [
            'users' => User::invitableForMeals()->get(),
        ]);
    }

    // Returns true when the current user is Admin or Chef.
    private function canManage(): bool
    {
        $role = auth()->user()?->role?->name;

        return in_array($role, ['Admin', 'Chef'], true);
    }

    /*
     * Returns true if the given user has meal notifications turned on
     * (or hasn't picked a setting yet, in which case the default is on).
     */
    private function userHasMealNotificationsEnabled(User $user): bool
    {
        $setting = $user->notificationSettings()
            ->where('notification_type_id', 4) // mealNotifications = id 4
            ->first();

        if (! $setting) {
            return true;
        }

        try {
            return filter_var($setting->pivot->value, FILTER_VALIDATE_BOOLEAN);
        } catch (\Exception $e) {
            return true;
        }
    }
}
