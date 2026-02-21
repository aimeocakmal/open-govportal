<?php

namespace App\Policies;

use App\Models\StaffDirectory;
use App\Models\User;

class StaffDirectoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_staff_directories');
    }

    public function view(User $user, StaffDirectory $record): bool
    {
        return $user->can('view_staff_directories');
    }

    public function create(User $user): bool
    {
        return $user->can('create_staff_directories');
    }

    public function update(User $user, StaffDirectory $record): bool
    {
        return $user->can('edit_staff_directories');
    }

    public function delete(User $user, StaffDirectory $record): bool
    {
        return $user->can('delete_staff_directories');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_staff_directories');
    }
}
