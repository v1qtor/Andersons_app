<?php

namespace App\Livewire\Meals;

use App\Models\Meal;
use App\Models\PlannedMeal;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AddMealModal extends Component
{
    public bool $showModal = false;

    public string $name = '';
    public string $date = '';
    public string $time = '';
    public array $invitees = [];
    public string $notes = '';

    protected $listeners = ['openAddMeal' => 'openModal'];

    public function openModal(): void
    {
        $this->reset(['name', 'date', 'time', 'invitees', 'notes']);
        $this->resetValidation();
        $this->showModal = true;
    }

    public function toggleInvitee(int $userId): void
    {
        if (in_array($userId, $this->invitees)) {
            $this->invitees = array_values(array_diff($this->invitees, [$userId]));
        } else {
            $this->invitees[] = $userId;
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'time' => ['required', 'date_format:H:i'],
            'invitees' => ['array'],
            'invitees.*' => ['exists:users,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $meal = Meal::firstOrCreate(
            ['name' => trim($this->name)],
        );

        $plannedMeal = PlannedMeal::create([
            'meal_id' => $meal->id,
            'date_time' => $this->date . ' ' . $this->time,
            'notes' => $this->notes ?: null,
        ]);

        if (!empty($this->invitees)) {
            $plannedMeal->subscribers()->attach($this->invitees);
        }

        // Notify chef if not added by chef
        $currentUser = Auth::user();
        $isChef = $currentUser && $currentUser->role && $currentUser->role->name === 'Chef';
        
        if (!$isChef) {
            $chef = User::whereHas('role', fn ($q) => $q->where('name', 'Chef'))->first();
            if ($chef) {
                $notification = UserNotification::create([
                    'user_id' => $chef->id,
                    'from_user_id' => $currentUser->id,
                    'title' => 'New Meal Scheduled',
                    'message' => $currentUser->name . ' scheduled a meal: ' . $meal->name . ' on ' . $plannedMeal->date_time->format('M d, H:i'),
                    'type' => 'meal_assignment',
                    'action_url' => '/meals',
                ]);

                broadcast(new \App\Events\NotificationCreated($notification));
            }
        }

        // Notify all invitees
        if (!empty($this->invitees)) {
            foreach ($this->invitees as $inviteeId) {
                $notification = UserNotification::create([
                    'user_id' => $inviteeId,
                    'from_user_id' => $currentUser->id,
                    'title' => 'You\'re Invited to a Meal',
                    'message' => 'You\'ve been invited to ' . $meal->name . ' on ' . $plannedMeal->date_time->format('M d, H:i'),
                    'type' => 'meal_assignment',
                    'action_url' => '/meals',
                ]);

                broadcast(new \App\Events\NotificationCreated($notification));
            }
        }

        $this->showModal = false;
        $this->dispatch('mealCreated');
    }

    public function render()
    {
        return view('livewire.meals.add-meal-modal', [
            // Chef schedules meals — they are never an invitee
            'users' => User::with('role')
                ->whereHas('role', fn ($q) => $q->where('name', '!=', 'Chef'))
                ->orderBy('name')
                ->get(),
        ]);
    }
}
