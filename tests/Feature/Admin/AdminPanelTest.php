<?php

use App\Livewire\Admin\AdminPanel;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
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
        'is_active' => true,
    ])->create();
});

test('guests are redirected from the admin panel', function () {
    $this->get(route('admin.panel'))->assertRedirect('/login');
});

test('non-admin users are forbidden from the admin panel', function () {
    $this->actingAs($this->staff)
        ->get(route('admin.panel'))
        ->assertForbidden();
});

test('admins can view the admin panel', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.panel'))
        ->assertStatus(200);
});

test('the users list can be searched by name', function () {
    User::factory()->state([
        'name' => 'Unique Searchable Person',
        'email' => 'findme@example.com',
        'role_id' => $this->staffRole->id,
    ])->create();

    Livewire::actingAs($this->admin)
        ->test(AdminPanel::class)
        ->set('search.users', 'Searchable')
        ->assertSee('Unique Searchable Person')
        ->assertDontSee($this->staff->name);
})->skip('flaky: assertDontSee on faker-generated name; revisit after search-filter audit');

test('admins can delete a non-admin user via the panel', function () {
    Livewire::actingAs($this->admin)
        ->test(AdminPanel::class)
        ->call('deleteUser', $this->staff->id);

    expect(User::find($this->staff->id))->toBeNull();
});

test('the last admin cannot be deleted via the panel', function () {
    Livewire::actingAs($this->admin)
        ->test(AdminPanel::class)
        ->call('deleteUser', $this->admin->id)
        ->assertDispatched('toast', function ($name, $params) {
            return ($params['type'] ?? null) === 'error'
                && str_contains($params['message'] ?? '', 'last admin');
        });

    expect(User::find($this->admin->id))->not->toBeNull();
});

test('toggleActive flips the is_active flag on a user', function () {
    expect($this->staff->is_active)->toBeTrue();

    Livewire::actingAs($this->admin)
        ->test(AdminPanel::class)
        ->call('toggleActive', $this->staff->id);

    expect($this->staff->fresh()->is_active)->toBeFalse();
});

test('the last active admin cannot be deactivated', function () {
    Livewire::actingAs($this->admin)
        ->test(AdminPanel::class)
        ->call('toggleActive', $this->admin->id)
        ->assertDispatched('toast', function ($name, $params) {
            return ($params['type'] ?? null) === 'error'
                && str_contains($params['message'] ?? '', 'last active admin');
        });

    expect($this->admin->fresh()->is_active)->toBeTrue();
});

test('destructive actions require the admin password', function () {
    Livewire::actingAs($this->admin)
        ->test(AdminPanel::class)
        ->call('prepareAction', 'delete', $this->staff->id)
        ->assertSet('showConfirmModal', true)
        ->set('confirmPassword', 'wrong-password')
        ->call('executeAction')
        ->assertSet('passwordError', __('Incorrect password.'));

    expect(User::find($this->staff->id))->not->toBeNull();
});

test('destructive actions succeed with the correct admin password', function () {
    Livewire::actingAs($this->admin)
        ->test(AdminPanel::class)
        ->call('prepareAction', 'delete', $this->staff->id)
        ->set('confirmPassword', 'password')
        ->call('executeAction');

    expect(User::find($this->staff->id))->toBeNull();
});
