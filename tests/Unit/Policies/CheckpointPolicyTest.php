<?php

use App\Models\Checkpoint;
use App\Policies\CheckpointPolicy;

/*
| Pure unit tests: in-memory model instances only, no database. The
| Checkpoint itself is irrelevant to every ability here (they only
| look at the user's role), so an unsaved instance is enough.
*/
beforeEach(function () {
    $this->policy = new CheckpointPolicy;
    $this->checkpoint = new Checkpoint;
});

test('household admins and family members can create, update and delete checkpoints', function (string $role) {
    $user = userWithRole($role);

    expect($this->policy->create($user))->toBeTrue()
        ->and($this->policy->update($user, $this->checkpoint))->toBeTrue()
        ->and($this->policy->delete($user, $this->checkpoint))->toBeTrue();
})->with(['Admin', 'The Andersons', 'Family Member']);

test('staff and chef cannot create, update or delete checkpoints', function (string $role) {
    $user = userWithRole($role);

    expect($this->policy->create($user))->toBeFalse()
        ->and($this->policy->update($user, $this->checkpoint))->toBeFalse()
        ->and($this->policy->delete($user, $this->checkpoint))->toBeFalse();
})->with(['Staff', 'Chef']);

test('a user with no role cannot manage checkpoints', function () {
    $user = userWithRole(null);

    expect($this->policy->create($user))->toBeFalse();
});
