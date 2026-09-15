<?php

use App\Models\Receipt;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    Storage::fake('public');
});

test('guests are redirected from a receipt file', function () {
    $receipt = Receipt::factory()->create(['file_path' => 'receipts/owner.pdf']);
    Storage::disk('public')->put('receipts/owner.pdf', 'fake pdf content');

    $this->get(route('receipts.show', 'owner.pdf'))->assertRedirect('/login');
});

test('the owner can view their own receipt file', function () {
    $owner = User::factory()->state(['role_id' => Role::where('name', 'Staff')->first()->id])->create();
    $receipt = Receipt::factory()->create(['user_id' => $owner->id, 'file_path' => 'receipts/owner.pdf']);
    Storage::disk('public')->put('receipts/owner.pdf', 'fake pdf content');

    $this->actingAs($owner)
        ->get(route('receipts.show', 'owner.pdf'))
        ->assertOk();
});

test('a household admin can view any receipt file', function () {
    $owner = User::factory()->state(['role_id' => Role::where('name', 'Staff')->first()->id])->create();
    $admin = User::factory()->state(['role_id' => Role::where('name', 'Admin')->first()->id])->create();
    $receipt = Receipt::factory()->create(['user_id' => $owner->id, 'file_path' => 'receipts/owner.pdf']);
    Storage::disk('public')->put('receipts/owner.pdf', 'fake pdf content');

    $this->actingAs($admin)
        ->get(route('receipts.show', 'owner.pdf'))
        ->assertOk();
});

test('another non-admin user cannot view someone else\'s receipt file', function () {
    $owner = User::factory()->state(['role_id' => Role::where('name', 'Staff')->first()->id])->create();
    $other = User::factory()->state(['role_id' => Role::where('name', 'Chef')->first()->id])->create();
    $receipt = Receipt::factory()->create(['user_id' => $owner->id, 'file_path' => 'receipts/owner.pdf']);
    Storage::disk('public')->put('receipts/owner.pdf', 'fake pdf content');

    $this->actingAs($other)
        ->get(route('receipts.show', 'owner.pdf'))
        ->assertForbidden();
});

test('a nonexistent receipt path 404s', function () {
    $user = User::factory()->state(['role_id' => Role::where('name', 'Admin')->first()->id])->create();

    $this->actingAs($user)
        ->get(route('receipts.show', 'does-not-exist.pdf'))
        ->assertNotFound();
});
