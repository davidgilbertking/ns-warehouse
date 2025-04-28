<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Product;
use App\DTOs\ProductStoreDTO;
use App\DTOs\ProductUpdateDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository
{
    public function paginateWithItemCount(int $perPage = 10): LengthAwarePaginator
    {
        return Product::withCount('items')->paginate($perPage);
    }

    public function all(): Collection
    {
        return Product::all();
    }

    public function findWithItems(int $id): ?Product
    {
        return Product::with('items')->find($id);
    }

    public function create(ProductStoreDTO $dto): Product
    {
        return Product::create([
                                   'name' => $dto->name,
                               ]);
    }

    public function update(Product $product, ProductUpdateDTO $dto): bool
    {
        return $product->update([
                                    'name' => $dto->name,
                                ]);
    }

    public function delete(Product $product): bool
    {
        return $product->delete();
    }

    public function attachItems(Product $product, array $items): void
    {
        $product->items()->attach(
            collect($items)->mapWithKeys(function ($item) {
                return [(int)$item['id'] => ['quantity' => (int)$item['quantity']]];
            })->toArray()
        );
    }

    public function syncItems(Product $product, array $items): void
    {
        $product->items()->sync(
            collect($items)->mapWithKeys(function ($item) {
                return [(int)$item['id'] => ['quantity' => (int)$item['quantity']]];
            })->toArray()
        );
    }
}
