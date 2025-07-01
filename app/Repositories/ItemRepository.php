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
    public function paginateWithFilters(ItemFilterDTO $filter, int $perPage = 10, int $depth = 0): LengthAwarePaginator
    {
        return $this->buildQueryWithFilters($filter, $depth)->paginate($perPage);
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

    public function getForExport(ItemFilterDTO $filter, int $depth = 0): Collection
    {
        return $this->buildQueryWithFilters($filter, $depth)->get();
    }

    private function buildQueryWithFilters(ItemFilterDTO $filter, int $depth): \Illuminate\Database\Eloquent\Builder
    {
        $query = Item::with(['products', 'reservations.event'])
                     ->where('depth', $depth); // Единый источник правды для depth

        if ($filter->getSearch()) {
            $search = $filter->getSearch();
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', '%' . $search . '%')
                  ->orWhere('description', 'ILIKE', '%' . $search . '%')
                  ->orWhere('size', 'ILIKE', '%' . $search . '%')
                  ->orWhere('material', 'ILIKE', '%' . $search . '%')
                  ->orWhere('supplier', 'ILIKE', '%' . $search . '%')
                  ->orWhere('storage_location', 'ILIKE', '%' . $search . '%')
                  ->orWhere('mechanics', 'ILIKE', '%' . $search . '%')
                  ->orWhere('scalability', 'ILIKE', '%' . $search . '%')
                  ->orWhere('branding_options', 'ILIKE', '%' . $search . '%')
                  ->orWhere('adaptation_options', 'ILIKE', '%' . $search . '%')
                  ->orWhere('op_price', 'ILIKE', '%' . $search . '%')
                  ->orWhere('construction_description', 'ILIKE', '%' . $search . '%')
                  ->orWhere('contractor', 'ILIKE', '%' . $search . '%')
                  ->orWhere('production_cost', 'ILIKE', '%' . $search . '%')
                  ->orWhere('change_history', 'ILIKE', '%' . $search . '%')
                  ->orWhere('consumables', 'ILIKE', '%' . $search . '%')
                  ->orWhere('implementation_comments', 'ILIKE', '%' . $search . '%')
                  ->orWhere('mounting', 'ILIKE', '%' . $search . '%')
                  ->orWhere('storage_features', 'ILIKE', '%' . $search . '%')
                  ->orWhere('design_links', 'ILIKE', '%' . $search . '%')
                  ->orWhere('event_history', 'ILIKE', '%' . $search . '%')
                  ->orWhere('storage_place', 'ILIKE', '%' . $search . '%')
                  ->orWhereHas('products', function ($q2) use ($search) {
                      $q2->where('name', 'ILIKE', '%' . $search . '%');
                  });
            });
        }

        if ($filter->getProduct()) {
            $query->whereHas('products', function ($q) use ($filter) {
                $q->where('name', 'ILIKE', '%' . $filter->getProduct() . '%');
            });
        }

        $query->orderBy('name', 'asc');

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
