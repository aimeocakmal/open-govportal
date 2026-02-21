<?php

namespace App\Policies;

use App\Models\StaticPage;
use App\Models\User;

class StaticPagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_static_pages');
    }

    public function view(User $user, StaticPage $record): bool
    {
        return $user->can('view_static_pages');
    }

    public function create(User $user): bool
    {
        return $user->can('create_static_pages');
    }

    public function update(User $user, StaticPage $record): bool
    {
        return $user->can('edit_static_pages');
    }

    public function delete(User $user, StaticPage $record): bool
    {
        return $user->can('delete_static_pages');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_static_pages');
    }
}
