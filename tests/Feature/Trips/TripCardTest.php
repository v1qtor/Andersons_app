<?php

use App\Livewire\Trips\TripCard;
use App\Models\AttachedFile;
use App\Models\Checkpoint;
use App\Models\Role;
use App\Models\Trip;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\TripCategorySeeder;
use Database\Seeders\TripStatusSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->seed(TripCategorySeeder::class);
    $this->seed(TripStatusSeeder::class);

    $this->admin = User::factory()->state([
        'email' => 'admin-card@example.com',
        'role_id' => Role::where('name', 'Admin')->first()->id,
    ])->create();

    $this->staff = User::factory()->state([
        'email' => 'staff-card@example.com',
        'role_id' => Role::where('name', 'Staff')->first()->id,
    ])->create();

    $this->trip = makeTrip(['name' => 'Card Trip']);
});

test('a manager can cancel a trip', function () {
    Livewire::actingAs($this->admin)
        ->test(TripCard::class, ['tripId' => $this->trip->id])
        ->call('cancel');

    expect($this->trip->fresh()->status->name)->toBe('cancelled');
});

test('a non-manager cannot cancel a trip', function () {
    Livewire::actingAs($this->staff)
        ->test(TripCard::class, ['tripId' => $this->trip->id])
        ->call('cancel')
        ->assertStatus(403);

    expect($this->trip->fresh()->status->name)->not->toBe('cancelled');
});

test('a manager can delete a trip', function () {
    Livewire::actingAs($this->admin)
        ->test(TripCard::class, ['tripId' => $this->trip->id])
        ->call('delete');

    expect(Trip::find($this->trip->id))->toBeNull();
});

test('a non-manager cannot delete a trip', function () {
    Livewire::actingAs($this->staff)
        ->test(TripCard::class, ['tripId' => $this->trip->id])
        ->call('delete')
        ->assertStatus(403);

    expect(Trip::find($this->trip->id))->not->toBeNull();
});

test('deleting a trip also deletes its temporary checkpoints but keeps permanent ones', function () {
    $permanent = Checkpoint::factory()->create();
    $temporary = Checkpoint::factory()->create();
    $this->trip->checkpoints()->attach($permanent->id, ['order' => 1, 'is_temporary' => false]);
    $this->trip->checkpoints()->attach($temporary->id, ['order' => 2, 'is_temporary' => true]);

    Livewire::actingAs($this->admin)
        ->test(TripCard::class, ['tripId' => $this->trip->id])
        ->call('delete');

    expect(Checkpoint::find($permanent->id))->not->toBeNull()
        ->and(Checkpoint::find($temporary->id))->toBeNull();
});

test('a non-manager cannot add or remove checkpoints', function () {
    $checkpoint = Checkpoint::factory()->create();

    Livewire::actingAs($this->staff)
        ->test(TripCard::class, ['tripId' => $this->trip->id])
        ->set('newCheckpointId', $checkpoint->id)
        ->call('addCheckpoint')
        ->assertStatus(403);

    expect($this->trip->fresh()->checkpoints)->toHaveCount(0);
});

test('a manager can add a permanent checkpoint to a trip', function () {
    $checkpoint = Checkpoint::factory()->create();

    Livewire::actingAs($this->admin)
        ->test(TripCard::class, ['tripId' => $this->trip->id])
        ->set('newCheckpointId', $checkpoint->id)
        ->call('addCheckpoint');

    expect($this->trip->fresh()->checkpoints->pluck('id')->all())->toContain($checkpoint->id);
});

test('any authenticated user can mark a checkpoint arrived and undo it', function () {
    $checkpoint = Checkpoint::factory()->create();
    $this->trip->checkpoints()->attach($checkpoint->id, ['order' => 1]);

    Livewire::actingAs($this->staff)
        ->test(TripCard::class, ['tripId' => $this->trip->id])
        ->call('markArrived', $checkpoint->id);

    $pivot = $this->trip->fresh()->checkpoints()->find($checkpoint->id)->pivot;
    expect((bool) $pivot->is_confirmed)->toBeTrue();

    Livewire::actingAs($this->staff)
        ->test(TripCard::class, ['tripId' => $this->trip->id])
        ->call('unmarkArrived', $checkpoint->id);

    $pivot = $this->trip->fresh()->checkpoints()->find($checkpoint->id)->pivot;
    expect((bool) $pivot->is_confirmed)->toBeFalse();
});

test('reordering swaps two checkpoints order values', function () {
    $first = Checkpoint::factory()->create();
    $second = Checkpoint::factory()->create();
    $this->trip->checkpoints()->attach($first->id, ['order' => 1]);
    $this->trip->checkpoints()->attach($second->id, ['order' => 2]);

    Livewire::actingAs($this->admin)
        ->test(TripCard::class, ['tripId' => $this->trip->id])
        ->call('reorderDown', $first->id);

    $ordered = $this->trip->fresh()->checkpoints()->orderByPivot('order')->get();
    expect($ordered->first()->id)->toBe($second->id)
        ->and($ordered->last()->id)->toBe($first->id);
});

test('a non-manager cannot upload a trip document', function () {
    Livewire::actingAs($this->staff)
        ->test(TripCard::class, ['tripId' => $this->trip->id])
        ->call('uploadDocument')
        ->assertStatus(403);
});

test('a manager can upload and remove a trip document', function () {
    Storage::fake('public');
    $file = UploadedFile::fake()->create('permit.pdf', 100, 'application/pdf');

    Livewire::actingAs($this->admin)
        ->test(TripCard::class, ['tripId' => $this->trip->id])
        ->set('newDocument', $file)
        ->call('uploadDocument')
        ->assertHasNoErrors();

    $attached = AttachedFile::where('trip_id', $this->trip->id)->first();
    expect($attached)->not->toBeNull();
    Storage::disk('public')->assertExists($attached->file_path);

    Livewire::actingAs($this->admin)
        ->test(TripCard::class, ['tripId' => $this->trip->id])
        ->call('confirmRemoveDocument', $attached->id)
        ->call('removeDocument');

    Storage::disk('public')->assertMissing($attached->file_path);
    expect(AttachedFile::find($attached->id))->toBeNull();
});
