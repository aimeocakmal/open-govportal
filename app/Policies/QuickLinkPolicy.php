<?php

namespace App\Policies;

use App\Models\QuickLink;
use App\Models\User;

class QuickLinkPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_quick_links');
    }

    public function view(User $user, QuickLink $record): bool
    {
        return $user->can('view_quick_links');
    }

    public function create(User $user): bool
    {
        return $user->can('create_quick_links');
    }

    public function update(User $user, QuickLink $record): bool
    {
        return $user->can('edit_quick_links');
    }

    public function delete(User $user, QuickLink $record): bool
    {
        return $user->can('delete_quick_links');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_quick_links');
    }
}
