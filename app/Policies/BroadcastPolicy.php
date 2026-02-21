<?php

namespace App\Policies;

use App\Models\Broadcast;
use App\Models\User;

class BroadcastPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_broadcasts');
    }

    public function view(User $user, Broadcast $record): bool
    {
        return $user->can('view_broadcasts');
    }

    public function create(User $user): bool
    {
        return $user->can('create_broadcasts');
    }

    public function update(User $user, Broadcast $record): bool
    {
        return $user->can('edit_broadcasts');
    }

    public function delete(User $user, Broadcast $record): bool
    {
        return $user->can('delete_broadcasts');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_broadcasts');
    }
}
