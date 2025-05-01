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
            $search = mb_strtolower($filter->getSearch(), 'UTF-8');
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . $search . '%'])
                  ->orWhereRaw('LOWER(description) LIKE ?', ['%' . $search . '%'])
                  ->orWhereRaw('LOWER(size) LIKE ?', ['%' . $search . '%'])
                  ->orWhereRaw('LOWER(material) LIKE ?', ['%' . $search . '%']);
            });
        }

        \Log::info('SQL', [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings(),
            'search_raw' => $filter->getSearch(),
        ]);

        return $query;
    }

    public function allWithQuantities(): Collection
    {
        return Item::select('id', 'name', 'quantity')->get();
    }

    public function findWithRelations(int $id): ?Item
    {
        return Item::with(['products', 'reservations.event'])->find($id);
    }

    public function getAvailableQuantityForItem(
        int  $itemId, string $startDate, string $endDate,
        ?int $excludeEventId = null
    ): int {
        $item = Item::find($itemId);

        if (!$item) {
            return 0;
        }

        $reserved = $item->reservations()
                         ->whereHas('event', function ($query) use ($startDate, $endDate, $excludeEventId) {
                             $query->where(function ($q) use ($startDate, $endDate) {
                                 $q->whereBetween('start_date', [$startDate, $endDate])
                                   ->orWhereBetween('end_date', [$startDate, $endDate])
                                   ->orWhere(function ($q2) use ($startDate, $endDate) {
                                       $q2->where('start_date', '<=', $startDate)
                                          ->where('end_date', '>=', $endDate);
                                   });
                             });

                             if ($excludeEventId) {
                                 $query->where('id', '!=', $excludeEventId);
                             }
                         })
                         ->sum('quantity');

        return max(0, $item->quantity - $reserved);
    }

    public function all(): \Illuminate\Database\Eloquent\Collection
    {
        return Item::all();
    }

    public function find(int $id): ?Item
    {
        return Item::find($id);
    }
}
