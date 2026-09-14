<?php

use App\Livewire\Trips\TripFormModal;
use App\Models\Checkpoint;
use App\Models\Role;
use App\Models\Trip;
use App\Models\TripCategory;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\TripCategorySeeder;
use Database\Seeders\TripStatusSeeder;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->seed(TripCategorySeeder::class);
    $this->seed(TripStatusSeeder::class);

    $this->admin = User::factory()->state([
        'email' => 'admin-form@example.com',
        'role_id' => Role::where('name', 'Admin')->first()->id,
    ])->create();

    $this->familyMember = User::factory()->state([
        'email' => 'family-form@example.com',
        'role_id' => Role::where('name', 'Family Member')->first()->id,
    ])->create();

    $this->staff = User::factory()->state([
        'email' => 'staff-form@example.com',
        'role_id' => Role::where('name', 'Staff')->first()->id,
    ])->create();

    $this->chef = User::factory()->state([
        'email' => 'chef-form@example.com',
        'role_id' => Role::where('name', 'Chef')->first()->id,
    ])->create();

    $this->category = TripCategory::first();

    Http::fake([
        'nominatim.openstreetmap.org/*' => Http::response([
            ['lat' => '51.5074', 'lon' => '-0.1278', 'display_name' => '221B Baker Street, London'],
        ], 200),
    ]);
});

test('staff and chef are forbidden from opening the create form', function () {
    Livewire::actingAs($this->staff)
        ->test(TripFormModal::class)
        ->call('open')
        ->assertStatus(403);

    Livewire::actingAs($this->chef)
        ->test(TripFormModal::class)
        ->call('open')
        ->assertStatus(403);
});

test('family members and admins can open and submit the create form', function () {
    Livewire::actingAs($this->familyMember)
        ->test(TripFormModal::class)
        ->call('open')
        ->set('name', 'Family Ski Trip')
        ->set('start_date', now()->addWeek()->format('Y-m-d\TH:i'))
        ->set('end_date', now()->addWeek()->addDays(3)->format('Y-m-d\TH:i'))
        ->set('trip_category_id', $this->category->id)
        ->set('userIds', [$this->familyMember->id])
        ->call('save')
        ->assertHasNoErrors();

    expect(Trip::where('name', 'Family Ski Trip')->exists())->toBeTrue();
});

test('name, dates and category are required', function () {
    Livewire::actingAs($this->admin)
        ->test(TripFormModal::class)
        ->call('open')
        ->set('userIds', [$this->admin->id])
        ->call('save')
        ->assertHasErrors(['name', 'start_date', 'end_date', 'trip_category_id']);
});

test('the end date must be after the start date', function () {
    Livewire::actingAs($this->admin)
        ->test(TripFormModal::class)
        ->call('open')
        ->set('name', 'Bad Dates Trip')
        ->set('start_date', now()->addWeek()->format('Y-m-d\TH:i'))
        ->set('end_date', now()->format('Y-m-d\TH:i'))
        ->set('trip_category_id', $this->category->id)
        ->set('userIds', [$this->admin->id])
        ->call('save')
        ->assertHasErrors(['end_date' => 'after']);
});

test('a future trip is saved with the upcoming status', function () {
    Livewire::actingAs($this->admin)
        ->test(TripFormModal::class)
        ->call('open')
        ->set('name', 'Future Trip')
        ->set('start_date', now()->addWeek()->format('Y-m-d\TH:i'))
        ->set('end_date', now()->addWeek()->addDays(2)->format('Y-m-d\TH:i'))
        ->set('trip_category_id', $this->category->id)
        ->set('userIds', [$this->admin->id])
        ->call('save');

    $trip = Trip::where('name', 'Future Trip')->first();
    expect($trip->status->name)->toBe('upcoming');
});

test('a trip spanning today is saved with the active status', function () {
    Livewire::actingAs($this->admin)
        ->test(TripFormModal::class)
        ->call('open')
        ->set('name', 'Ongoing Trip')
        ->set('start_date', now()->subDay()->format('Y-m-d\TH:i'))
        ->set('end_date', now()->addDay()->format('Y-m-d\TH:i'))
        ->set('trip_category_id', $this->category->id)
        ->set('userIds', [$this->admin->id])
        ->call('save');

    $trip = Trip::where('name', 'Ongoing Trip')->first();
    expect($trip->status->name)->toBe('active');
});

test('participants and permanent checkpoints are attached', function () {
    $checkpoint = Checkpoint::factory()->create(['location' => 'Old Trafford']);

    Livewire::actingAs($this->admin)
        ->test(TripFormModal::class)
        ->call('open')
        ->set('name', 'Football Trip')
        ->set('start_date', now()->addWeek()->format('Y-m-d\TH:i'))
        ->set('end_date', now()->addWeek()->addDay()->format('Y-m-d\TH:i'))
        ->set('trip_category_id', $this->category->id)
        ->set('userIds', [$this->admin->id, $this->familyMember->id])
        ->set('checkpointIds', [$checkpoint->id])
        ->call('save');

    $trip = Trip::where('name', 'Football Trip')->first();
    expect($trip->users->pluck('id')->all())->toContain($this->admin->id, $this->familyMember->id)
        ->and($trip->checkpoints->pluck('id')->all())->toContain($checkpoint->id);
});

test('a temporary checkpoint is geocoded and attached as this-trip-only', function () {
    Livewire::actingAs($this->admin)
        ->test(TripFormModal::class)
        ->call('open')
        ->set('name', 'Geocoded Trip')
        ->set('start_date', now()->addWeek()->format('Y-m-d\TH:i'))
        ->set('end_date', now()->addWeek()->addDay()->format('Y-m-d\TH:i'))
        ->set('trip_category_id', $this->category->id)
        ->set('userIds', [$this->admin->id])
        ->call('addTempCheckpoint')
        ->set('tempCheckpoints.0.name', 'Sherlock\'s House')
        ->set('tempCheckpoints.0.address', '221B Baker Street, London')
        ->call('save');

    $trip = Trip::where('name', 'Geocoded Trip')->first();
    $checkpoint = $trip->checkpoints->firstWhere('location', 'Sherlock\'s House');

    expect($checkpoint)->not->toBeNull()
        ->and((float) $checkpoint->latitude)->toBe(51.5074)
        ->and((float) $checkpoint->longitude)->toBe(-0.1278)
        ->and((bool) $checkpoint->pivot->is_temporary)->toBeTrue();
});

test('plus-ones are created for the trip', function () {
    Livewire::actingAs($this->admin)
        ->test(TripFormModal::class)
        ->call('open')
        ->set('name', 'Guest Trip')
        ->set('start_date', now()->addWeek()->format('Y-m-d\TH:i'))
        ->set('end_date', now()->addWeek()->addDay()->format('Y-m-d\TH:i'))
        ->set('trip_category_id', $this->category->id)
        ->set('userIds', [$this->admin->id])
        ->call('addPlusOne')
        ->set('plusOnes.0.name', 'John Doe')
        ->set('plusOnes.0.email', 'john@example.com')
        ->call('save');

    $trip = Trip::where('name', 'Guest Trip')->first();
    expect($trip->plusOnes)->toHaveCount(1)
        ->and($trip->plusOnes->first()->name)->toBe('John Doe');
});

test('opening the form for an existing trip prefills its fields', function () {
    $trip = makeTrip(['name' => 'Existing Trip']);
    $trip->users()->attach($this->familyMember->id, ['is_organizer' => true]);

    Livewire::actingAs($this->admin)
        ->test(TripFormModal::class)
        ->call('open', $trip->id)
        ->assertSet('name', 'Existing Trip')
        ->assertSet('userIds', [(string) $this->familyMember->id]);
});

test('editing a trip updates its fields and syncs participants', function () {
    $trip = makeTrip(['name' => 'Old Name']);
    $trip->users()->attach($this->familyMember->id, ['is_organizer' => true]);

    Livewire::actingAs($this->admin)
        ->test(TripFormModal::class)
        ->call('open', $trip->id)
        ->set('name', 'New Name')
        ->set('userIds', [$this->admin->id])
        ->call('save');

    $trip->refresh();
    expect($trip->name)->toBe('New Name')
        ->and($trip->users->pluck('id')->all())->toBe([$this->admin->id]);
});
