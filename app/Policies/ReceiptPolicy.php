<?php

namespace App\Policies;

use App\Models\Receipt;
use App\Models\User;

/*
| Who can see and change expense reports. `view`/`update`/`delete` are
| deliberately different thresholds: an owner can always *see* their
| own paid invoice, but can no longer *change* it once it's paid
| (only a household admin can). Household admins can do everything.
*/
class ReceiptPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role?->name, ['Staff', 'Chef', 'Admin', 'The Andersons'], true);
    }

    public function view(User $user, Receipt $receipt): bool
    {
        return $user->isHouseholdAdmin() || $receipt->user_id === $user->id;
    }

    public function update(User $user, Receipt $receipt): bool
    {
        if ($user->isHouseholdAdmin()) {
            return true;
        }

        return $receipt->user_id === $user->id && ! $receipt->is_paid;
    }

    public function delete(User $user, Receipt $receipt): bool
    {
        return $this->update($user, $receipt);
    }

    public function markPaid(User $user, Receipt $receipt): bool
    {
        return $user->isHouseholdAdmin();
    }
}
