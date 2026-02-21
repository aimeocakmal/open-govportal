<?php

namespace App\Policies;

use App\Models\Media;
use App\Models\User;

class MediaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_media');
    }

    public function view(User $user, Media $record): bool
    {
        return $user->can('view_media');
    }

    public function create(User $user): bool
    {
        return $user->can('create_media');
    }

    public function update(User $user, Media $record): bool
    {
        return $user->can('edit_media');
    }

    public function delete(User $user, Media $record): bool
    {
        return $user->can('delete_media');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_media');
    }
}
