<?php

namespace App\Policies;

use App\Models\PageCategory;
use App\Models\User;

class PageCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_page_categories');
    }

    public function view(User $user, PageCategory $record): bool
    {
        return $user->can('view_page_categories');
    }

    public function create(User $user): bool
    {
        return $user->can('create_page_categories');
    }

    public function update(User $user, PageCategory $record): bool
    {
        return $user->can('edit_page_categories');
    }

    public function delete(User $user, PageCategory $record): bool
    {
        return $user->can('delete_page_categories');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_page_categories');
    }
}
