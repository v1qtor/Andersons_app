<?php

use App\Livewire\Trips\TripManager;
use App\Models\Role;
use App\Models\Status;
use App\Models\TripCategory;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\TripCategorySeeder;
use Database\Seeders\TripStatusSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->seed(TripCategorySeeder::class);
    $this->seed(TripStatusSeeder::class);

    $this->familyMember = User::factory()->state([
        'email' => 'family-trips@example.com',
        'role_id' => Role::where('name', 'Family Member')->first()->id,
    ])->create();

    $this->staff = User::factory()->state([
        'email' => 'staff-trips@example.com',
        'role_id' => Role::where('name', 'Staff')->first()->id,
    ])->create();

    $this->category = TripCategory::first();
});

test('guests are redirected from the trips page', function () {
    $this->get(route('trips.index'))->assertRedirect('/login');
});

test('any authenticated user can view the trips page', function () {
    $this->actingAs($this->staff)
        ->get(route('trips.index'))
        ->assertStatus(200);
});

test('a trip and its checkpoints render on the full page', function () {
    $trip = makeTrip(['name' => 'Rendered Trip']);
    $checkpoint = \App\Models\Checkpoint::factory()->create(['location' => 'Rendered Checkpoint']);
    $trip->checkpoints()->attach($checkpoint->id, ['order' => 1]);

    $this->actingAs($this->staff)
        ->get(route('trips.index'))
        ->assertOk()
        ->assertSee('Rendered Trip')
        ->assertSee('Rendered Checkpoint');
});

test('only users who can manage trips see the Plan Trip button', function () {
    Livewire::actingAs($this->familyMember)
        ->test(TripManager::class)
        ->assertSee('Plan Trip');

    Livewire::actingAs($this->staff)
        ->test(TripManager::class)
        ->assertDontSee('Plan Trip');
});

test('searching filters trips by name', function () {
    makeTrip(['name' => 'Trip To Bristol']);
    makeTrip(['name' => 'Trip To Leeds']);

    $names = Livewire::actingAs($this->staff)
        ->test(TripManager::class)
        ->set('search', 'Bristol')
        ->instance()
        ->getTrips()
        ->pluck('name')
        ->all();

    expect($names)->toContain('Trip To Bristol')->not->toContain('Trip To Leeds');
});

test('the status filter only shows trips with the matching status', function () {
    $cancelledStatus = Status::where('name', 'cancelled')->where('type', 'trip')->first();
    makeTrip(['name' => 'Upcoming Trip']);
    makeTrip(['name' => 'Cancelled Trip', 'status_id' => $cancelledStatus->id]);

    $names = Livewire::actingAs($this->staff)
        ->test(TripManager::class)
        ->set('statusFilter', 'cancelled')
        ->instance()
        ->getTrips()
        ->pluck('name')
        ->all();

    expect($names)->toContain('Cancelled Trip')->not->toContain('Upcoming Trip');
});
