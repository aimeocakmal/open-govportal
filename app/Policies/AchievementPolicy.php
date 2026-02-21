<?php

namespace App\Policies;

use App\Models\Achievement;
use App\Models\User;

class AchievementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_achievements');
    }

    public function view(User $user, Achievement $record): bool
    {
        return $user->can('view_achievements');
    }

    public function create(User $user): bool
    {
        return $user->can('create_achievements');
    }

    public function update(User $user, Achievement $record): bool
    {
        return $user->can('edit_achievements');
    }

    public function delete(User $user, Achievement $record): bool
    {
        return $user->can('delete_achievements');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_achievements');
    }
}
