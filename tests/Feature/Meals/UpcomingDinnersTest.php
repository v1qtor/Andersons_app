<?php

use App\Livewire\Meals\UpcomingDinners;
use App\Models\Meal;
use App\Models\MealGuest;
use App\Models\PlannedMeal;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $this->family = User::factory()->state([
        'email' => 'family-dinners@example.com',
        'role_id' => Role::where('name', 'Family Member')->first()->id,
    ])->create();

    $this->otherFamily = User::factory()->state([
        'email' => 'other-family-dinners@example.com',
        'role_id' => Role::where('name', 'Family Member')->first()->id,
    ])->create();

    $meal = Meal::factory()->create(['name' => 'Pasta']);
    $this->plannedMeal = PlannedMeal::factory()->create([
        'meal_id' => $meal->id,
        'date_time' => now()->addDay(),
    ]);
    $this->plannedMeal->subscribers()->attach($this->family->id, ['confirmed' => true]);
});

test('saveGuests creates new, updates existing, and deletes removed guests', function () {
    $existingToKeep = MealGuest::create([
        'planned_meal_id' => $this->plannedMeal->id,
        'invited_by_user_id' => $this->family->id,
        'name' => 'Old Name',
        'note' => 'old note',
    ]);

    $existingToDrop = MealGuest::create([
        'planned_meal_id' => $this->plannedMeal->id,
        'invited_by_user_id' => $this->family->id,
        'name' => 'Will be dropped',
        'note' => null,
    ]);

    Livewire::actingAs($this->family)
        ->test(UpcomingDinners::class)
        ->call('startGuestEdit', $this->plannedMeal->id)
        ->set('guests', [
            ['id' => $existingToKeep->id, 'name' => 'Updated Name', 'note' => 'updated note'],
            ['id' => null, 'name' => 'Brand New Guest', 'note' => ''],
        ])
        ->call('saveGuests', $this->plannedMeal->id);

    $remaining = MealGuest::where('planned_meal_id', $this->plannedMeal->id)
        ->where('invited_by_user_id', $this->family->id)
        ->get();

    expect($remaining)->toHaveCount(2)
        ->and(MealGuest::find($existingToDrop->id))->toBeNull()
        ->and($existingToKeep->fresh()->name)->toBe('Updated Name')
        ->and($existingToKeep->fresh()->note)->toBe('updated note')
        ->and($remaining->pluck('name'))->toContain('Brand New Guest');
});

test('a user cannot save guests for a meal they have not joined', function () {
    Livewire::actingAs($this->otherFamily)
        ->test(UpcomingDinners::class)
        ->set('guests', [['id' => null, 'name' => 'Sneaky Guest', 'note' => '']])
        ->call('saveGuests', $this->plannedMeal->id);

    expect(MealGuest::where('planned_meal_id', $this->plannedMeal->id)->count())->toBe(0);
});

test('a user cannot tamper with another users guest row', function () {
    $othersGuest = MealGuest::create([
        'planned_meal_id' => $this->plannedMeal->id,
        'invited_by_user_id' => $this->family->id,
        'name' => 'Belongs to family',
        'note' => 'private',
    ]);

    $this->plannedMeal->subscribers()->attach($this->otherFamily->id, ['confirmed' => true]);

    Livewire::actingAs($this->otherFamily)
        ->test(UpcomingDinners::class)
        ->call('startGuestEdit', $this->plannedMeal->id)
        ->set('guests', [
            ['id' => $othersGuest->id, 'name' => 'HACKED', 'note' => 'hacked note'],
        ])
        ->call('saveGuests', $this->plannedMeal->id);

    expect($othersGuest->fresh()->name)->toBe('Belongs to family')
        ->and($othersGuest->fresh()->note)->toBe('private');
});
