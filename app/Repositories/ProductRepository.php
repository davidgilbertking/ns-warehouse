<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Product;

class ProductRepository
{
    public function all(): \Illuminate\Database\Eloquent\Collection
    {
        return Product::all();
    }

    public function findWithItems(int $id): ?Product
    {
        return Product::with('items')->find($id);
    }
}
