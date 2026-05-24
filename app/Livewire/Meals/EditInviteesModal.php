<?php

namespace App\Livewire\Meals;

use App\Events\NotificationCreated;
use App\Models\PlannedMeal;
use App\Models\User;
use App\Models\UserNotification;
use Livewire\Attributes\On;
use Livewire\Component;

class EditInviteesModal extends Component
{
    public bool $showModal = false;
    public ?int $mealId = null;
    public string $mealName = '';
    public string $mealDateTime = '';
    public array $invitees = [];

    // Open the modal for a given meal — managers (Admin/Chef) only.
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

    public function rules(): array
    {
        return [
            'invitees'   => ['array'],
            'invitees.*' => ['exists:users,id'],
        ];
    }

    // Diff staged invitees against current pivot, attach/detach accordingly, and notify newly invited users only.
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

        $current = $meal->subscribers()->pluck('users.id')->map(fn ($id) => (int) $id)->all();
        $staged  = array_map('intval', $this->invitees);

        $toAdd    = array_values(array_diff($staged, $current));
        $toRemove = array_values(array_diff($current, $staged));

        if (! empty($toRemove)) {
            $meal->subscribers()->detach($toRemove);
        }

        if (! empty($toAdd)) {
            $meal->subscribers()->attach(
                collect($toAdd)->mapWithKeys(fn ($id) => [$id => ['confirmed' => false]])->all()
            );

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

        $this->showModal = false;
        $this->dispatch('mealUpdated');
    }

    public function render()
    {
        return view('livewire.meals.edit-invitees-modal', [
            'users' => User::invitableForMeals()->get(),
        ]);
    }

    private function canManage(): bool
    {
        $role = auth()->user()?->role?->name;

        return in_array($role, ['Admin', 'Chef'], true);
    }

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
