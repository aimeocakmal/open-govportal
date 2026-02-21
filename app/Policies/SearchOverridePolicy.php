<?php

namespace App\Policies;

use App\Models\SearchOverride;
use App\Models\User;

class SearchOverridePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_search_overrides');
    }

    public function view(User $user, SearchOverride $record): bool
    {
        return $user->can('view_search_overrides');
    }

    public function create(User $user): bool
    {
        return $user->can('create_search_overrides');
    }

    public function update(User $user, SearchOverride $record): bool
    {
        return $user->can('edit_search_overrides');
    }

    public function delete(User $user, SearchOverride $record): bool
    {
        return $user->can('delete_search_overrides');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_search_overrides');
    }
}
