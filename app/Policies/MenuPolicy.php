<?php

namespace App\Policies;

use App\Models\Menu;
use App\Models\User;

class MenuPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage_settings');
    }

    public function view(User $user, Menu $menu): bool
    {
        return $user->can('manage_settings');
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Menu $menu): bool
    {
        return $user->can('manage_settings');
    }

    public function delete(User $user, Menu $menu): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
