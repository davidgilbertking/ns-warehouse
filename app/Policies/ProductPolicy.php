<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function update(User $user, Product $product): bool
    {
        return $user->role === 'admin'; // Только админ может редактировать продукт
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->role === 'admin'; // И удалять тоже только админ
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }
}
