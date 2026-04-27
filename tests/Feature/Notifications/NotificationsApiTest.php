<?php

use App\Models\Role;
use App\Models\User;
use App\Models\UserNotification;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $staffRoleId = Role::where('name', 'Staff')->first()->id;

    $this->userA = User::factory()->state([
        'email' => 'user-a-notif@example.com',
        'role_id' => $staffRoleId,
    ])->create();

    $this->userB = User::factory()->state([
        'email' => 'user-b-notif@example.com',
        'role_id' => $staffRoleId,
    ])->create();
});

function makeNotification(User $user, array $overrides = []): UserNotification
{
    return UserNotification::create(array_merge([
        'user_id' => $user->id,
        'from_user_id' => $user->id,
        'title' => 'Test Notification',
        'message' => 'Test message',
        'type' => 'info',
    ], $overrides));
}

test('guests cannot access the notifications api', function () {
    $this->getJson('/api/notifications')->assertStatus(401);
});

test('the notifications api only returns the authenticated users notifications', function () {
    makeNotification($this->userA, ['title' => 'For A']);
    makeNotification($this->userA, ['title' => 'Also for A']);
    makeNotification($this->userB, ['title' => 'For B']);

    $response = $this->actingAs($this->userA)->getJson('/api/notifications');

    $response->assertStatus(200);
    $titles = collect($response->json('notifications'))->pluck('title')->all();

    expect($titles)->toContain('For A', 'Also for A')
        ->and($titles)->not->toContain('For B');
});

test('a user can mark their own notification as read', function () {
    $notification = makeNotification($this->userA, ['is_read' => false]);

    $this->actingAs($this->userA)
        ->post(route('notifications.mark-as-read', $notification->id))
        ->assertStatus(200);

    expect($notification->fresh()->is_read)->toBeTrue();
});

test('a user cannot mark another users notification as read', function () {
    $notification = makeNotification($this->userB, ['is_read' => false]);

    $this->actingAs($this->userA)
        ->post(route('notifications.mark-as-read', $notification->id))
        ->assertStatus(403);

    expect($notification->fresh()->is_read)->toBeFalse();
});

test('a user can delete their own notification', function () {
    $notification = makeNotification($this->userA);

    $this->actingAs($this->userA)
        ->delete(route('notifications.delete', $notification->id))
        ->assertStatus(200);

    expect(UserNotification::find($notification->id))->toBeNull();
});

test('a user cannot delete another users notification', function () {
    $notification = makeNotification($this->userB);

    $this->actingAs($this->userA)
        ->delete(route('notifications.delete', $notification->id))
        ->assertStatus(403);

    expect(UserNotification::find($notification->id))->not->toBeNull();
});

test('clear-all only deletes the authenticated users notifications', function () {
    makeNotification($this->userA);
    makeNotification($this->userA);
    $bNotification = makeNotification($this->userB);

    $this->actingAs($this->userA)
        ->post(route('notifications.clear-all'))
        ->assertStatus(200);

    expect(UserNotification::where('user_id', $this->userA->id)->count())->toBe(0)
        ->and(UserNotification::find($bNotification->id))->not->toBeNull();
});
