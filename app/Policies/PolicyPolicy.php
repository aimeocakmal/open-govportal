<?php

namespace App\Policies;

use App\Models\Policy;
use App\Models\User;

class PolicyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_policies');
    }

    public function view(User $user, Policy $record): bool
    {
        return $user->can('view_policies');
    }

    public function create(User $user): bool
    {
        return $user->can('create_policies');
    }

    public function update(User $user, Policy $record): bool
    {
        return $user->can('edit_policies');
    }

    public function delete(User $user, Policy $record): bool
    {
        return $user->can('delete_policies');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_policies');
    }
}
