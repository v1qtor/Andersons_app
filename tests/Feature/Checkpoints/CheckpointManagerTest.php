<?php

use App\Livewire\Checkpoints\CheckpointManager;
use App\Models\Checkpoint;
use App\Models\Folder;
use App\Models\Role;
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

    $this->user = User::factory()->state([
        'email' => 'checkpoints-user@example.com',
        'role_id' => Role::where('name', 'Family Member')->first()->id,
    ])->create();

    Http::fake([
        'nominatim.openstreetmap.org/*' => Http::response([
            ['lat' => '54.0749', 'lon' => '-2.1628', 'display_name' => 'Malham Cove, Settle'],
        ], 200),
    ]);
});

test('guests are redirected from the checkpoints page', function () {
    $this->get(route('checkpoints.index'))->assertRedirect('/login');
});

test('any authenticated user can view the checkpoints page', function () {
    $this->actingAs($this->user)
        ->get(route('checkpoints.index'))
        ->assertStatus(200);
});

test('a checkpoint can be created with a new folder and geocoded address', function () {
    Livewire::actingAs($this->user)
        ->test(CheckpointManager::class)
        ->call('openCreate')
        ->set('location', 'Malham Cove')
        ->set('description', 'Natural limestone formation')
        ->set('address', 'Malham Cove, Settle BD24 9PT')
        ->set('newFolderName', 'Yorkshire Dales')
        ->call('save')
        ->assertHasNoErrors();

    $checkpoint = Checkpoint::where('location', 'Malham Cove')->first();
    expect($checkpoint)->not->toBeNull()
        ->and((float) $checkpoint->latitude)->toBe(54.0749)
        ->and((float) $checkpoint->longitude)->toBe(-2.1628)
        ->and($checkpoint->user_id)->toBe($this->user->id)
        ->and($checkpoint->folder->name)->toBe('Yorkshire Dales');
});

test('a checkpoint requires a name', function () {
    Livewire::actingAs($this->user)
        ->test(CheckpointManager::class)
        ->call('openCreate')
        ->call('save')
        ->assertHasErrors(['location']);

    expect(Checkpoint::count())->toBe(0);
});

test('opening the form for an existing checkpoint prefills its fields', function () {
    $folder = Folder::factory()->create(['name' => 'Lake District']);
    $checkpoint = Checkpoint::factory()->create([
        'location' => 'Windermere',
        'address' => 'Windermere, Cumbria',
        'folder_id' => $folder->id,
    ]);

    Livewire::actingAs($this->user)
        ->test(CheckpointManager::class)
        ->call('openEdit', $checkpoint->id)
        ->assertSet('location', 'Windermere')
        ->assertSet('address', 'Windermere, Cumbria')
        ->assertSet('folderId', $folder->id);
});

test('editing a checkpoint updates its fields', function () {
    $checkpoint = Checkpoint::factory()->create(['location' => 'Old Name']);

    Livewire::actingAs($this->user)
        ->test(CheckpointManager::class)
        ->call('openEdit', $checkpoint->id)
        ->set('location', 'New Name')
        ->call('save');

    expect($checkpoint->fresh()->location)->toBe('New Name');
});

test('a checkpoint can be deleted', function () {
    $checkpoint = Checkpoint::factory()->create();

    Livewire::actingAs($this->user)
        ->test(CheckpointManager::class)
        ->call('delete', $checkpoint->id);

    expect(Checkpoint::find($checkpoint->id))->toBeNull();
});

test('deleting a checkpoint detaches it from any trips', function () {
    $checkpoint = Checkpoint::factory()->create();
    $trip = makeTrip();
    $trip->checkpoints()->attach($checkpoint->id, ['order' => 1]);

    Livewire::actingAs($this->user)
        ->test(CheckpointManager::class)
        ->call('delete', $checkpoint->id);

    expect($trip->fresh()->checkpoints)->toHaveCount(0);
});

test('searching filters checkpoints by name, description and folder', function () {
    $folder = Folder::factory()->create(['name' => 'Peak District']);
    Checkpoint::factory()->create(['location' => 'Kinder Scout', 'folder_id' => $folder->id]);
    Checkpoint::factory()->create(['location' => 'Old Trafford', 'folder_id' => null]);

    $names = Livewire::actingAs($this->user)
        ->test(CheckpointManager::class)
        ->set('search', 'Peak District')
        ->instance()
        ->getCheckpoints()
        ->pluck('location');

    expect($names)->toContain('Kinder Scout')->not->toContain('Old Trafford');
});
