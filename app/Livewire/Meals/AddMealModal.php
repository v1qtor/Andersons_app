<?php

namespace App\Livewire\Meals;

use App\Events\NotificationCreated;
use App\Models\Meal;
use App\Models\PlannedMeal;
use App\Models\User;
use App\Models\UserNotification;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Livewire\Component;

/*
| AddMealModal: class half of a reusable nested Livewire component
| (paired with resources/views/livewire/meals/add-meal-modal.blade.php).
| Owns the "Add New Meal" popup: collects a dish name, date, time,
| invitees and notes; on save creates the meal and sends notifications
| to the chef and everyone invited.
|
| Models: Meal (reuses or creates a dish by name), PlannedMeal (the
| new scheduled meal), User (looks up the chef and the invitees),
| UserNotification (one row per recipient).
*/
class AddMealModal extends Component
{
    // Whether the popup is currently open.
    public bool $showModal = false;

    // Form fields bound to the inputs in the view.
    public string $name = '';

    public string $date = '';

    public string $time = '';

    public array $invitees = [];

    public string $notes = '';

    // Opens the popup with a clean form whenever another component
    // dispatches 'openAddMeal' (the "Add Meal" button on the page).
    #[On('openAddMeal')]
    public function openModal(): void
    {
        $this->reset(['name', 'date', 'time', 'invitees', 'notes']);
        $this->resetValidation();
        $this->showModal = true;
    }

    /*
     * Returns the validation rules for the form: name must be filled,
     * date must be today or later, time together with the date must
     * not be in the past, invitees must be real users, and notes are
     * optional with a length cap.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'time' => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) {
                    if (! $this->date) {
                        return;
                    }
                    if (Carbon::parse($this->date.' '.$value)->isPast()) {
                        $fail(__('The meal cannot be scheduled in the past.'));
                    }
                },
            ],
            'invitees' => ['array'],
            'invitees.*' => ['exists:users,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    // Custom error messages shown for specific rule failures.
    public function messages(): array
    {
        return [
            'date.after_or_equal' => __('The meal cannot be scheduled in the past.'),
        ];
    }

    /*
     * Saves the form: validates input, creates (or reuses) the dish,
     * stores the planned meal with its invitees, sends notifications
     * to the chef (if someone else added it) and to every invitee,
     * then closes the popup and asks the meal list to refresh.
     */
    public function save(): void
    {
        $this->validate();

        // Reuse the existing dish with this name if there is one,
        // otherwise create a new entry.
        $meal = Meal::firstOrCreate(
            ['name' => trim($this->name)],
        );

        // Store the new scheduled meal on the chosen date and time.
        $plannedMeal = PlannedMeal::create([
            'meal_id' => $meal->id,
            'date_time' => $this->date.' '.$this->time,
            'notes' => $this->notes ?: null,
        ]);

        // Add the chosen invitees to the meal.
        if (! empty($this->invitees)) {
            $plannedMeal->subscribers()->attach($this->invitees);
        }

        // If the Chef scheduled the meal themselves they don't need to be told.
        $currentUser = auth()->user();
        $isChef = $currentUser && $currentUser->role && $currentUser->role->name === 'Chef';

        // Otherwise let the Chef know a new meal was scheduled.
        if (! $isChef) {
            $chef = User::whereHas('role', fn ($q) => $q->where('name', 'Chef'))->first();
            if ($chef && $this->userHasMealNotificationsEnabled($chef)) {
                $notification = UserNotification::create([
                    'user_id' => $chef->id,
                    'from_user_id' => $currentUser->id,
                    'title' => 'New Meal Scheduled',
                    'message' => $currentUser->name.' scheduled a meal: '.$meal->name.' on '.$plannedMeal->date_time->format('M d, H:i'),
                    'type' => 'meal_assignment',
                    'action_url' => '/meals',
                ]);

                broadcast(new NotificationCreated($notification));
            }
        }

        // Send each invitee a notification so they see the invitation
        // on their dashboard (only those who haven't disabled it).
        if (! empty($this->invitees)) {
            foreach ($this->invitees as $inviteeId) {
                $invitee = User::find($inviteeId);
                if ($invitee && $this->userHasMealNotificationsEnabled($invitee)) {
                    $notification = UserNotification::create([
                        'user_id' => $inviteeId,
                        'from_user_id' => $currentUser->id,
                        'title' => 'You\'re Invited to a Meal',
                        'message' => 'You\'ve been invited to '.$meal->name.' on '.$plannedMeal->date_time->format('M d, H:i'),
                        'type' => 'meal_assignment',
                        'action_url' => '/meals',
                    ]);

                    broadcast(new NotificationCreated($notification));
                }
            }
        }

        // Close the popup and tell the MealList component to refresh
        // so the new meal shows up immediately.
        $this->showModal = false;
        $this->dispatch('mealCreated');
    }

    /*
     * Builds the data the view needs: the list of people who can be
     * invited (everyone in the household except the Chef).
     */
    public function render()
    {
        return view('livewire.meals.add-meal-modal', [
            'users' => User::invitableForMeals()->get(),
        ]);
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
            return true; // Default to enabled if not set
        }

        try {
            return filter_var($setting->pivot->value, FILTER_VALIDATE_BOOLEAN);
        } catch (\Exception $e) {
            return true; // Default to enabled if decode fails
        }
    }
}
