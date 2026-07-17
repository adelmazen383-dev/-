<?php

namespace App\Policies;

use App\Models\Contract;
use App\Models\User;

class ContractPolicy
{
    /**
     * Anyone authenticated can view contracts list.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Anyone authenticated can view a single contract.
     */
    public function view(User $user, Contract $contract): bool
    {
        return true;
    }

    /**
     * Anyone authenticated can create contracts.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Only admins can cancel contracts.
     */
    public function cancel(User $user, Contract $contract): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Only admins can delete contracts.
     */
    public function delete(User $user, Contract $contract): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Only admins can manage users.
     */
    public function manageUsers(User $user): bool
    {
        return $user->hasRole('admin');
    }
}
