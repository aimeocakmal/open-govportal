<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Activitylog\Models\Activity;

class ActivityLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_activity_logs');
    }

    public function view(User $user, Activity $record): bool
    {
        return $user->can('view_activity_logs');
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Activity $record): bool
    {
        return false;
    }

    public function delete(User $user, Activity $record): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
