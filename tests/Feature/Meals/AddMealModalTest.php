<?php

use App\Events\NotificationCreated;
use App\Livewire\Meals\AddMealModal;
use App\Models\Meal;
use App\Models\NotificationType;
use App\Models\PlannedMeal;
use App\Models\Role;
use App\Models\User;
use App\Models\UserNotification;
use Database\Seeders\NotificationTypeSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->seed(NotificationTypeSeeder::class);
    Event::fake([NotificationCreated::class]);

    $this->admin = User::factory()->state([
        'email' => 'admin-add-meal@example.com',
        'role_id' => Role::where('name', 'Admin')->first()->id,
    ])->create();

    $this->chef = User::factory()->state([
        'email' => 'chef-add-meal@example.com',
        'role_id' => Role::where('name', 'Chef')->first()->id,
    ])->create();

    $this->family = User::factory()->state([
        'email' => 'family-add-meal@example.com',
        'role_id' => Role::where('name', 'Family Member')->first()->id,
    ])->create();

    $this->otherFamily = User::factory()->state([
        'email' => 'other-family-add-meal@example.com',
        'role_id' => Role::where('name', 'Family Member')->first()->id,
    ])->create();
});

test('a meal cannot be scheduled on a past date', function () {
    Livewire::actingAs($this->admin)
        ->test(AddMealModal::class)
        ->call('openModal')
        ->set('name', 'Pasta')
        ->set('date', now()->subDay()->toDateString())
        ->set('time', '18:00')
        ->call('save')
        ->assertHasErrors(['date' => 'after_or_equal']);

    expect(PlannedMeal::count())->toBe(0);
});

test('a meal cannot be scheduled at a past time today', function () {
    $oneMinuteAgo = now()->subMinute()->format('H:i');

    Livewire::actingAs($this->admin)
        ->test(AddMealModal::class)
        ->call('openModal')
        ->set('name', 'Pasta')
        ->set('date', now()->toDateString())
        ->set('time', $oneMinuteAgo)
        ->call('save')
        ->assertHasErrors('time');

    expect(PlannedMeal::count())->toBe(0);
});

test('scheduling two meals with the same name reuses the Meal row', function () {
    Livewire::actingAs($this->admin)
        ->test(AddMealModal::class)
        ->call('openModal')
        ->set('name', 'Sunday Roast')
        ->set('date', now()->addDay()->toDateString())
        ->set('time', '18:00')
        ->call('save');

    Livewire::actingAs($this->admin)
        ->test(AddMealModal::class)
        ->call('openModal')
        ->set('name', 'Sunday Roast')
        ->set('date', now()->addDays(2)->toDateString())
        ->set('time', '18:00')
        ->call('save');

    expect(Meal::where('name', 'Sunday Roast')->count())->toBe(1)
        ->and(PlannedMeal::count())->toBe(2);
});

test('invitees are attached as unconfirmed subscribers', function () {
    Livewire::actingAs($this->admin)
        ->test(AddMealModal::class)
        ->call('openModal')
        ->set('name', 'Pasta')
        ->set('date', now()->addDay()->toDateString())
        ->set('time', '18:00')
        ->set('invitees', [$this->family->id, $this->otherFamily->id])
        ->call('save')
        ->assertHasNoErrors();

    $planned = PlannedMeal::first();
    expect($planned->subscribers)->toHaveCount(2);

    foreach ($planned->subscribers as $subscriber) {
        expect((bool) $subscriber->pivot->confirmed)->toBeFalse();
    }
});

test('the chef receives a notification when a non-chef schedules a meal', function () {
    Livewire::actingAs($this->family)
        ->test(AddMealModal::class)
        ->call('openModal')
        ->set('name', 'Pasta')
        ->set('date', now()->addDay()->toDateString())
        ->set('time', '18:00')
        ->call('save')
        ->assertHasNoErrors();

    $chefNotification = UserNotification::where('user_id', $this->chef->id)
        ->where('type', 'meal_assignment')
        ->first();

    expect($chefNotification)->not->toBeNull()
        ->and($chefNotification->from_user_id)->toBe($this->family->id);
});

test('the chef does not receive a notification when the chef schedules the meal', function () {
    Livewire::actingAs($this->chef)
        ->test(AddMealModal::class)
        ->call('openModal')
        ->set('name', 'Pasta')
        ->set('date', now()->addDay()->toDateString())
        ->set('time', '18:00')
        ->call('save')
        ->assertHasNoErrors();

    expect(UserNotification::where('user_id', $this->chef->id)->count())->toBe(0);
});

test('invitees with meal notifications disabled receive no notification', function () {
    $this->family->notificationSettings()->attach(
        NotificationType::find(4)->id,
        ['value' => false]
    );

    Livewire::actingAs($this->admin)
        ->test(AddMealModal::class)
        ->call('openModal')
        ->set('name', 'Pasta')
        ->set('date', now()->addDay()->toDateString())
        ->set('time', '18:00')
        ->set('invitees', [$this->family->id])
        ->call('save')
        ->assertHasNoErrors();

    expect(UserNotification::where('user_id', $this->family->id)->count())->toBe(0);
});

test('saving a meal dispatches the mealCreated event', function () {
    Livewire::actingAs($this->admin)
        ->test(AddMealModal::class)
        ->call('openModal')
        ->set('name', 'Pasta')
        ->set('date', now()->addDay()->toDateString())
        ->set('time', '18:00')
        ->call('save')
        ->assertDispatched('mealCreated');
});
