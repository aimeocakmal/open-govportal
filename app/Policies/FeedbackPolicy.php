<?php

namespace App\Policies;

use App\Models\Feedback;
use App\Models\User;

class FeedbackPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_feedbacks');
    }

    public function view(User $user, Feedback $record): bool
    {
        return $user->can('view_feedbacks');
    }

    public function create(User $user): bool
    {
        return $user->can('create_feedbacks');
    }

    public function update(User $user, Feedback $record): bool
    {
        return $user->can('edit_feedbacks');
    }

    public function delete(User $user, Feedback $record): bool
    {
        return $user->can('delete_feedbacks');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_feedbacks');
    }
}
