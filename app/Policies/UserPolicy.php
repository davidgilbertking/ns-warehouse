<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function delete(User $authUser, User $user)
    {
        return $authUser->isAdmin() && !$user->isAdmin();
    }

    public function create(User $user)
    {
        return $user->isAdmin();
    }
}
