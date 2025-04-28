<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTOs\ItemFilterDTO;
use App\DTOs\ItemStoreDTO;
use App\DTOs\ItemUpdateDTO;
use App\Models\Item;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ItemRepository
{
    public function paginateWithFilters(ItemFilterDTO $filter, int $perPage = 10): LengthAwarePaginator
    {
        return $this->buildQueryWithFilters($filter)->paginate($perPage);
    }

    public function create(ItemStoreDTO $data): Item
    {
        return Item::create($data->toArray());
    }

    public function update(Item $item, ItemUpdateDTO $data): bool
    {
        return $item->update($data->toArray());
    }

    public function delete(Item $item): bool
    {
        return $item->delete();
    }

    public function getForExport(ItemFilterDTO $filter): Collection
    {
        return $this->buildQueryWithFilters($filter)->get();
    }


    private function buildQueryWithFilters(ItemFilterDTO $filter): \Illuminate\Database\Eloquent\Builder
    {
        $query = Item::with(['products', 'reservations.event']);

        if ($filter->getSearch()) {
            $search = strtolower($filter->getSearch());
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . $search . '%'])
                  ->orWhereRaw('LOWER(description) LIKE ?', ['%' . $search . '%'])
                  ->orWhereRaw('LOWER(size) LIKE ?', ['%' . $search . '%'])
                  ->orWhereRaw('LOWER(material) LIKE ?', ['%' . $search . '%']);
            });
        }

        return $query;
    }
}
