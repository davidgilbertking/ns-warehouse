<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ActivityLog;

class ActivityLogPolicy
{
    public function viewAny(User $user)
    {
        return $user->role === 'admin';
    }
}
