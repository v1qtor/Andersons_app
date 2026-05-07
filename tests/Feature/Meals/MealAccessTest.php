<?php

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $this->admin = User::factory()->state([
        'email' => 'admin-meals@example.com',
        'role_id' => Role::where('name', 'Admin')->first()->id,
    ])->create();

    $this->chef = User::factory()->state([
        'email' => 'chef-meals@example.com',
        'role_id' => Role::where('name', 'Chef')->first()->id,
    ])->create();

    $this->family = User::factory()->state([
        'email' => 'family-meals@example.com',
        'role_id' => Role::where('name', 'Family Member')->first()->id,
    ])->create();
});

test('guests cannot access any meals page', function () {
    $this->get(route('meals.index'))->assertRedirect('/login');
    $this->get(route('admin.meals.index'))->assertRedirect('/login');
    $this->get(route('chef.meals.index'))->assertRedirect('/login');
});

test('any authenticated user can view the meal schedule', function () {
    $this->actingAs($this->family)
        ->get(route('meals.index'))
        ->assertStatus(200);
});

test('only admins can access the admin meal planning page', function () {
    $this->actingAs($this->family)
        ->get(route('admin.meals.index'))
        ->assertForbidden();

    $this->actingAs($this->chef)
        ->get(route('admin.meals.index'))
        ->assertForbidden();

    $this->actingAs($this->admin)
        ->get(route('admin.meals.index'))
        ->assertStatus(200);
});

test('only chefs can access the chef meal planning page', function () {
    $this->actingAs($this->family)
        ->get(route('chef.meals.index'))
        ->assertForbidden();

    $this->actingAs($this->admin)
        ->get(route('chef.meals.index'))
        ->assertForbidden();

    $this->actingAs($this->chef)
        ->get(route('chef.meals.index'))
        ->assertStatus(200);
});
