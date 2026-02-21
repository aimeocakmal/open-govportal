<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage_users');
    }

    public function view(User $user, User $model): bool
    {
        return $user->can('manage_users');
    }

    public function create(User $user): bool
    {
        return $user->can('manage_users');
    }

    public function update(User $user, User $model): bool
    {
        return $user->can('manage_users');
    }

    public function delete(User $user, User $model): bool
    {
        // Cannot delete self
        if ($user->id === $model->id) {
            return false;
        }

        // Only super_admin can delete another super_admin
        if ($model->hasRole('super_admin') && ! $user->hasRole('super_admin')) {
            return false;
        }

        return $user->can('manage_users');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('manage_users');
    }
}
