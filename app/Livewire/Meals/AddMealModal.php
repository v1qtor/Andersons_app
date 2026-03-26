<?php

namespace App\Livewire\Meals;

use App\Models\Meal;
use App\Models\PlannedMeal;
use App\Models\User;
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
