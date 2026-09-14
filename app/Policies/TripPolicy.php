<?php

namespace App\Policies;

use App\Models\Trip;
use App\Models\User;

/*
| Who can plan and manage trips. Viewing a trip, checking in at a
| checkpoint, and uploading checkpoint photos are open to any
| authenticated user and aren't gated here — only the
| organizer-level actions are.
*/
class TripPolicy
{
    public function create(User $user): bool
    {
        return $user->canManageTrips();
    }

    public function update(User $user, Trip $trip): bool
    {
        return $user->canManageTrips();
    }

    public function delete(User $user, Trip $trip): bool
    {
        return $user->canManageTrips();
    }

    public function cancel(User $user, Trip $trip): bool
    {
        return $user->canManageTrips();
    }
}
