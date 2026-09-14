<?php

use App\Models\Trip;
use App\Policies\TripPolicy;

/*
| Pure unit tests: in-memory model instances only, no database. The
| Trip itself is irrelevant to every ability here (they only look at
| the user's role), so an unsaved instance is enough.
*/
beforeEach(function () {
    $this->policy = new TripPolicy;
    $this->trip = new Trip;
});

test('household admins and family members can create, update, delete and cancel trips', function (string $role) {
    $user = userWithRole($role);

    expect($this->policy->create($user))->toBeTrue()
        ->and($this->policy->update($user, $this->trip))->toBeTrue()
        ->and($this->policy->delete($user, $this->trip))->toBeTrue()
        ->and($this->policy->cancel($user, $this->trip))->toBeTrue();
})->with(['Admin', 'The Andersons', 'Family Member']);

test('staff and chef cannot create, update, delete or cancel trips', function (string $role) {
    $user = userWithRole($role);

    expect($this->policy->create($user))->toBeFalse()
        ->and($this->policy->update($user, $this->trip))->toBeFalse()
        ->and($this->policy->delete($user, $this->trip))->toBeFalse()
        ->and($this->policy->cancel($user, $this->trip))->toBeFalse();
})->with(['Staff', 'Chef']);

test('a user with no role cannot manage trips', function () {
    $user = userWithRole(null);

    expect($this->policy->create($user))->toBeFalse();
});
