<?php

use App\Livewire\Admin\UserCreate;
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

test('guests are redirected from the user create page', function () {
    $this->get(route('admin.users.create'))->assertRedirect('/login');
});

test('non-admin users are forbidden from the user create page', function () {
    $this->actingAs($this->staff)
        ->get(route('admin.users.create'))
        ->assertForbidden();
});

test('admins can view the user create page', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.users.create'))
        ->assertStatus(200);
});

test('empty submission fails validation', function () {
    Livewire::actingAs($this->admin)
        ->test(UserCreate::class)
        ->call('save')
        ->assertHasErrors(['name', 'email', 'password', 'iban', 'phone_number']);
});

test('duplicate email fails validation', function () {
    Livewire::actingAs($this->admin)
        ->test(UserCreate::class)
        ->set('name', 'Jane')
        ->set('email', $this->staff->email)
        ->set('password', 'secret123')
        ->set('password_confirmation', 'secret123')
        ->set('iban', 'GB00 TEST 0000 0000 0000 00')
        ->set('phone_number', '+44 7700 900999')
        ->call('save')
        ->assertHasErrors(['email' => 'unique']);
});

test('mismatched password confirmation fails validation', function () {
    Livewire::actingAs($this->admin)
        ->test(UserCreate::class)
        ->set('name', 'Jane')
        ->set('email', 'new@example.com')
        ->set('password', 'secret123')
        ->set('password_confirmation', 'different')
        ->set('iban', 'GB00 TEST 0000 0000 0000 00')
        ->set('phone_number', '+44 7700 900999')
        ->call('save')
        ->assertHasErrors(['password' => 'confirmed']);
});

test('short password fails validation', function () {
    Livewire::actingAs($this->admin)
        ->test(UserCreate::class)
        ->set('name', 'Jane')
        ->set('email', 'new@example.com')
        ->set('password', 'short')
        ->set('password_confirmation', 'short')
        ->set('iban', 'GB00 TEST 0000 0000 0000 00')
        ->set('phone_number', '+44 7700 900999')
        ->call('save')
        ->assertHasErrors(['password' => 'min']);
});

test('admins can create a new user', function () {
    Livewire::actingAs($this->admin)
        ->test(UserCreate::class)
        ->set('name', 'Jane Doe')
        ->set('email', 'jane@example.com')
        ->set('password', 'secret123')
        ->set('password_confirmation', 'secret123')
        ->set('iban', 'GB00 TEST 0000 0000 0000 00')
        ->set('phone_number', '+44 7700 900999')
        ->set('role_id', $this->staffRole->id)
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('admin.panel'));

    $created = User::where('email', 'jane@example.com')->first();

    expect($created)->not->toBeNull()
        ->and($created->name)->toBe('Jane Doe')
        ->and($created->role_id)->toBe($this->staffRole->id)
        ->and(Hash::check('secret123', $created->password))->toBeTrue();
});
