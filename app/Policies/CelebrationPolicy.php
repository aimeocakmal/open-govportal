<?php

namespace App\Policies;

use App\Models\Celebration;
use App\Models\User;

class CelebrationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_celebrations');
    }

    public function view(User $user, Celebration $record): bool
    {
        return $user->can('view_celebrations');
    }

    public function create(User $user): bool
    {
        return $user->can('create_celebrations');
    }

    public function update(User $user, Celebration $record): bool
    {
        return $user->can('edit_celebrations');
    }

    public function delete(User $user, Celebration $record): bool
    {
        return $user->can('delete_celebrations');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_celebrations');
    }
}
