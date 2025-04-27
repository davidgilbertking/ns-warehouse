<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function delete(User $authUser, User $user)
    {
        return $authUser->role === 'admin' && $user->role !== 'admin';
    }

    public function create(User $user)
    {
        return $user->role === 'admin';
    }
}
