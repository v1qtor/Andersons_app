<?php

use App\Livewire\TripAcceptance;
use App\Models\PlusOne;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\TripCategorySeeder;
use Database\Seeders\TripStatusSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->seed(TripCategorySeeder::class);
    $this->seed(TripStatusSeeder::class);

    $this->user = User::factory()->state([
        'email' => 'trip-acceptance-user@example.com',
        'role_id' => Role::where('name', 'Family Member')->first()->id,
    ])->create();

    $this->trip = makeTrip();
});

test('a user can accept a trip with a plus-one', function () {
    Livewire::actingAs($this->user)
        ->test(TripAcceptance::class, ['trip' => $this->trip])
        ->call('addPlusOne')
        ->set('plusOnes.0.name', 'Guest One')
        ->call('acceptTripWithPlusOnes')
        ->assertSet('acceptTrip', true);

    expect($this->user->trips()->where('trip_id', $this->trip->id)->exists())->toBeTrue()
        ->and(PlusOne::where('trip_id', $this->trip->id)->where('name', 'Guest One')->exists())->toBeTrue();
});

test('declining a trip requires confirmation before it takes effect', function () {
    $this->trip->users()->attach($this->user->id, ['is_organizer' => false]);

    Livewire::actingAs($this->user)
        ->test(TripAcceptance::class, ['trip' => $this->trip])
        ->assertSet('acceptTrip', true)
        ->call('confirmDecline')
        ->assertSet('confirmingDecline', true);

    // Still attached — confirming didn't act yet.
    expect($this->user->trips()->where('trip_id', $this->trip->id)->exists())->toBeTrue();
});

test('a user can decline a trip after confirming, removing their plus-ones', function () {
    $this->trip->users()->attach($this->user->id, ['is_organizer' => false]);
    PlusOne::create(['trip_id' => $this->trip->id, 'added_by' => $this->user->id, 'name' => 'Guest One']);

    Livewire::actingAs($this->user)
        ->test(TripAcceptance::class, ['trip' => $this->trip])
        ->call('confirmDecline')
        ->call('rejectTrip')
        ->assertSet('acceptTrip', false)
        ->assertSet('confirmingDecline', false);

    expect($this->user->trips()->where('trip_id', $this->trip->id)->exists())->toBeFalse()
        ->and(PlusOne::where('trip_id', $this->trip->id)->exists())->toBeFalse();
});
