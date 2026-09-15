<?php

namespace App\Policies;

use App\Models\Checkpoint;
use App\Models\User;

/*
| Who can manage the shared checkpoint library. Browsing/picking a
| checkpoint when planning a trip is open to any authenticated user
| and isn't gated here — only creating/editing/deleting entries in the
| shared library is, since a delete removes the checkpoint from every
| trip that references it.
*/
class CheckpointPolicy
{
    public function create(User $user): bool
    {
        return $user->canManageTrips();
    }

    public function update(User $user, Checkpoint $checkpoint): bool
    {
        return $user->canManageTrips();
    }

    public function delete(User $user, Checkpoint $checkpoint): bool
    {
        return $user->canManageTrips();
    }
}
