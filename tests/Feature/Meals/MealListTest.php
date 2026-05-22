<?php

use App\Livewire\Meals\MealList;
use App\Models\Meal;
use App\Models\PlannedMeal;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $this->admin = User::factory()->state([
        'email' => 'admin-meal-list@example.com',
        'role_id' => Role::where('name', 'Admin')->first()->id,
    ])->create();

    $this->chef = User::factory()->state([
        'email' => 'chef-meal-list@example.com',
        'role_id' => Role::where('name', 'Chef')->first()->id,
    ])->create();

    $this->family = User::factory()->state([
        'email' => 'family-meal-list@example.com',
        'role_id' => Role::where('name', 'Family Member')->first()->id,
    ])->create();

    $this->meal = Meal::factory()->create(['name' => 'Pasta']);

    $this->invitedMeal = PlannedMeal::factory()->create([
        'meal_id' => $this->meal->id,
        'date_time' => now()->addDay(),
    ]);
    $this->invitedMeal->subscribers()->attach($this->family->id, ['confirmed' => false]);

    $this->otherMeal = PlannedMeal::factory()->create([
        'meal_id' => $this->meal->id,
        'date_time' => now()->addDays(2),
    ]);
});

test('family members only see meals they are invited to', function () {
    $component = Livewire::actingAs($this->family)
        ->test(MealList::class);

    $meals = $component->viewData('meals');
    expect($meals->total())->toBe(1)
        ->and($meals->first()->id)->toBe($this->invitedMeal->id);
});

test('admins see every planned meal', function () {
    $component = Livewire::actingAs($this->admin)
        ->test(MealList::class);

    expect($component->viewData('meals')->total())->toBe(2);
});

test('toggleParticipation flips the confirmed pivot for an invited family member', function () {
    $component = Livewire::actingAs($this->family)
        ->test(MealList::class);

    $component->call('toggleParticipation', $this->invitedMeal->id);
    expect((bool) $this->invitedMeal->subscribers()->wherePivot('user_id', $this->family->id)->first()->pivot->confirmed)
        ->toBeTrue();

    $component->call('toggleParticipation', $this->invitedMeal->id);
    expect((bool) $this->invitedMeal->subscribers()->wherePivot('user_id', $this->family->id)->first()->pivot->confirmed)
        ->toBeFalse();
});

test('a chef cannot toggle participation (silent no-op)', function () {
    $this->invitedMeal->subscribers()->attach($this->chef->id, ['confirmed' => false]);

    Livewire::actingAs($this->chef)
        ->test(MealList::class)
        ->call('toggleParticipation', $this->invitedMeal->id);

    expect((bool) $this->invitedMeal->subscribers()->wherePivot('user_id', $this->chef->id)->first()->pivot->confirmed)
        ->toBeFalse();
});

test('a non-invitee cannot RSVP themselves into a meal via toggleParticipation', function () {
    Livewire::actingAs($this->family)
        ->test(MealList::class)
        ->call('toggleParticipation', $this->otherMeal->id);

    expect($this->otherMeal->subscribers()->wherePivot('user_id', $this->family->id)->exists())
        ->toBeFalse();
});

test('a family member cannot delete a meal even via the action handler', function () {
    Livewire::actingAs($this->family)
        ->test(MealList::class)
        ->call('deleteMeal', $this->invitedMeal->id)
        ->assertStatus(403);

    expect(PlannedMeal::find($this->invitedMeal->id))->not->toBeNull();
});

test('an admin can delete a meal', function () {
    Livewire::actingAs($this->admin)
        ->test(MealList::class)
        ->call('deleteMeal', $this->invitedMeal->id);

    expect(PlannedMeal::find($this->invitedMeal->id))->toBeNull();
});

test('only the chef can mark a meal as prepared', function () {
    Livewire::actingAs($this->family)
        ->test(MealList::class)
        ->call('togglePrepared', $this->invitedMeal->id)
        ->assertStatus(403);

    Livewire::actingAs($this->admin)
        ->test(MealList::class)
        ->call('togglePrepared', $this->invitedMeal->id)
        ->assertStatus(403);

    expect((bool) $this->invitedMeal->fresh()->is_prepared)->toBeFalse();

    Livewire::actingAs($this->chef)
        ->test(MealList::class)
        ->call('togglePrepared', $this->invitedMeal->id);

    expect((bool) $this->invitedMeal->fresh()->is_prepared)->toBeTrue();
});
