<?php

use App\Models\Receipt;
use App\Policies\ReceiptPolicy;

beforeEach(function () {
    $this->policy = new ReceiptPolicy();
});

test('viewAny is limited to staff-level and household admin roles', function (string $role) {
    expect($this->policy->viewAny(userWithRole($role)))->toBeTrue();
})->with(['Staff', 'Chef', 'Admin', 'The Andersons']);

test('viewAny is denied for family members and users with no role', function (?string $role) {
    expect($this->policy->viewAny(userWithRole($role)))->toBeFalse();
})->with(['Family Member', null]);

test('a household admin can view any receipt', function () {
    $owner = userWithRole('Staff');
    $owner->id = 1;
    $receipt = (new Receipt())->forceFill(['user_id' => 2]);

    expect($this->policy->view(userWithRole('Admin'), $receipt))->toBeTrue();
});

test('a staff member can view only their own receipt', function () {
    $owner = userWithRole('Staff');
    $owner->id = 1;
    $otherStaff = userWithRole('Staff');
    $otherStaff->id = 2;

    $receipt = (new Receipt())->forceFill(['user_id' => 1]);

    expect($this->policy->view($owner, $receipt))->toBeTrue()
        ->and($this->policy->view($otherStaff, $receipt))->toBeFalse();
});

test('an owner can update their own unpaid receipt but not once it is paid', function () {
    $owner = userWithRole('Staff');
    $owner->id = 1;

    $unpaid = (new Receipt())->forceFill(['user_id' => 1, 'is_paid' => false]);
    $paid = (new Receipt())->forceFill(['user_id' => 1, 'is_paid' => true]);

    expect($this->policy->update($owner, $unpaid))->toBeTrue()
        ->and($this->policy->update($owner, $paid))->toBeFalse()
        ->and($this->policy->delete($owner, $paid))->toBeFalse();
});

test('a household admin can update and delete a receipt even once paid', function () {
    $admin = userWithRole('Admin');
    $paid = (new Receipt())->forceFill(['user_id' => 99, 'is_paid' => true]);

    expect($this->policy->update($admin, $paid))->toBeTrue()
        ->and($this->policy->delete($admin, $paid))->toBeTrue();
});

test('only a household admin can mark a receipt as paid', function () {
    $admin = userWithRole('Admin');
    $staff = userWithRole('Staff');
    $receipt = new Receipt();

    expect($this->policy->markPaid($admin, $receipt))->toBeTrue()
        ->and($this->policy->markPaid($staff, $receipt))->toBeFalse();
});
