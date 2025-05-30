<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function update(User $user, Product $product): bool
    {
        return $user->isAdmin(); // Только админ может редактировать тэг
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->isAdmin(); // И удалять тоже только админ
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }
}
