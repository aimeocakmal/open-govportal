<?php

namespace App\Policies;

use App\Models\PolicyFile;
use App\Models\User;

class PolicyFilePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_files');
    }

    public function view(User $user, PolicyFile $record): bool
    {
        return $user->can('view_files');
    }

    public function create(User $user): bool
    {
        return $user->can('create_files');
    }

    public function update(User $user, PolicyFile $record): bool
    {
        return $user->can('edit_files');
    }

    public function delete(User $user, PolicyFile $record): bool
    {
        return $user->can('delete_files');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_files');
    }
}
