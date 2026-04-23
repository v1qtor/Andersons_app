<?php

use App\Livewire\Admin\UserEdit;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->adminRole = Role::where('name', 'Admin')->first();
    $this->staffRole = Role::where('name', 'Staff')->first();

    $this->admin = User::factory()->state([
        'email' => 'admin-test@example.com',
        'role_id' => $this->adminRole->id,
    ])->create();

    $this->staff = User::factory()->state([
        'email' => 'staff-test@example.com',
        'role_id' => $this->staffRole->id,
    ])->create();
});

test('guests are redirected from the user edit page', function () {
    $this->get(route('admin.users.edit', $this->staff))->assertRedirect('/login');
});

test('non-admin users are forbidden from the user edit page', function () {
    $this->actingAs($this->staff)
        ->get(route('admin.users.edit', $this->staff))
        ->assertForbidden();
});

test('edit form is prefilled with the user data', function () {
    Livewire::actingAs($this->admin)
        ->test(UserEdit::class, ['user' => $this->staff])
        ->assertSet('name', $this->staff->name)
        ->assertSet('email', $this->staff->email)
        ->assertSet('role_id', $this->staffRole->id);
});

test('admins can update a user without changing the password', function () {
    $originalHash = $this->staff->password;

    Livewire::actingAs($this->admin)
        ->test(UserEdit::class, ['user' => $this->staff])
        ->set('name', 'Renamed User')
        ->set('email', 'renamed@example.com')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('admin.panel'));

    $this->staff->refresh();
    expect($this->staff->name)->toBe('Renamed User')
        ->and($this->staff->email)->toBe('renamed@example.com')
        ->and($this->staff->password)->toBe($originalHash);
});

test('admins can update the password and it is hashed', function () {
    Livewire::actingAs($this->admin)
        ->test(UserEdit::class, ['user' => $this->staff])
        ->set('password', 'brand-new-pass')
        ->set('password_confirmation', 'brand-new-pass')
        ->call('save')
        ->assertHasNoErrors();

    $this->staff->refresh();
    expect(Hash::check('brand-new-pass', $this->staff->password))->toBeTrue();
});

test('email uniqueness ignores the user being edited', function () {
    Livewire::actingAs($this->admin)
        ->test(UserEdit::class, ['user' => $this->staff])
        ->set('email', $this->staff->email)
        ->call('save')
        ->assertHasNoErrors();
});

test('email must be unique against other users', function () {
    Livewire::actingAs($this->admin)
        ->test(UserEdit::class, ['user' => $this->staff])
        ->set('email', $this->admin->email)
        ->call('save')
        ->assertHasErrors(['email' => 'unique']);
});

test('delete is blocked when the admin password is wrong', function () {
    Livewire::actingAs($this->admin)
        ->test(UserEdit::class, ['user' => $this->staff])
        ->set('deletePassword', 'wrong-password')
        ->call('deleteUser')
        ->assertSet('deletePasswordError', __('Incorrect password.'));

    expect(User::find($this->staff->id))->not->toBeNull();
});

test('admins can delete a user with the correct password', function () {
    Livewire::actingAs($this->admin)
        ->test(UserEdit::class, ['user' => $this->staff])
        ->set('deletePassword', 'password')
        ->call('deleteUser')
        ->assertRedirect(route('admin.panel'));

    expect(User::find($this->staff->id))->toBeNull();
});

test('the last admin cannot be deleted', function () {
    $secondAdmin = User::factory()->state([
        'email' => 'admin2@example.com',
        'role_id' => $this->adminRole->id,
    ])->create();

    $this->admin->delete();

    Livewire::actingAs($secondAdmin)
        ->test(UserEdit::class, ['user' => $secondAdmin])
        ->set('deletePassword', 'password')
        ->call('deleteUser')
        ->assertSet('deletePasswordError', __('Cannot delete the last admin user.'));

    expect(User::find($secondAdmin->id))->not->toBeNull();
});
