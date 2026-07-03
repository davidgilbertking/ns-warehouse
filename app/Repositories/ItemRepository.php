<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTOs\ItemFilterDTO;
use App\DTOs\ItemStoreDTO;
use App\DTOs\ItemUpdateDTO;
use App\Models\Item;
use App\Support\UnicodeSearch;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ItemRepository
{
    public function paginateWithFilters(ItemFilterDTO $filter, int $perPage = 10, int $depth = 0): LengthAwarePaginator
    {
        return UnicodeSearch::paginate($this->getFilteredItems($filter, $depth), $perPage);
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
        return $this->getFilteredItems($filter, $depth);
    }

    private function getFilteredItems(ItemFilterDTO $filter, int $depth): Collection
    {
        $items = Item::with(['parentItems', 'products', 'reservations.event'])
            ->where('depth', $depth)
            ->orderBy('name', 'asc')
            ->get();

        if (UnicodeSearch::term($filter->getSearch()) !== null) {
            $items = $items->filter(fn (Item $item): bool => $this->matchesSearch($item, $filter->getSearch()));
        }

        if (UnicodeSearch::term($filter->getProduct()) !== null) {
            $items = $items->filter(
                fn (Item $item): bool => $item->products->contains(
                    fn ($product): bool => UnicodeSearch::contains($product->name, $filter->getProduct())
                )
            );
        }

        if (UnicodeSearch::term($filter->getStoragePlace()) !== null) {
            $items = $items->filter(
                fn (Item $item): bool => UnicodeSearch::contains($item->storage_place, $filter->getStoragePlace())
            );
        }

        return $items->values();
    }

    private function matchesSearch(Item $item, ?string $search): bool
    {
        return UnicodeSearch::containsAny([
            $item->name,
            $item->description,
            $item->size,
            $item->material,
            $item->supplier,
            $item->storage_location,
            $item->mechanics,
            $item->scalability,
            $item->branding_options,
            $item->adaptation_options,
            $item->op_price,
            $item->construction_description,
            $item->contractor,
            $item->production_cost,
            $item->change_history,
            $item->consumables,
            $item->implementation_comments,
            $item->mounting,
            $item->storage_features,
            $item->design_links,
            $item->event_history,
            $item->storage_place,
        ], $search) || $item->products->contains(
            fn ($product): bool => UnicodeSearch::contains($product->name, $search)
        );
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
        int $itemId, string $startDate, string $endDate,
        ?int $excludeEventId = null
    ): int {
        $item = Item::find($itemId);

        if (! $item) {
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
