<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\ItemFilterDTO;
use App\DTOs\ItemStoreDTO;
use App\DTOs\ItemUpdateDTO;
use App\Models\ActivityLog;
use App\Models\Item;
use App\Repositories\ItemRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ItemService
{
    public function __construct(
        protected ItemRepository $repository,
        protected ItemImageService $imageService,
        protected ItemVideoService $videoService
    ) {}

    public function getPaginatedItems(ItemFilterDTO $filter, int $depth, int $perPage = 10): LengthAwarePaginator
    {
        $availableFrom = $filter->getAvailableFrom();
        $availableTo = $filter->getAvailableTo();

        if ($availableFrom && $availableTo && $availableFrom > $availableTo) {
            throw new \InvalidArgumentException('Дата "От" не может быть позже даты "До".');
        }

        $items = $this->repository->paginateWithFilters($filter, $perPage, $depth);

        if ($availableFrom && $availableTo) {
            foreach ($items as $item) {
                $reserved = 0;

                foreach ($item->reservations as $reservation) {
                    $event = $reservation->event;
                    $filterFrom = Carbon::parse($availableFrom)->startOfDay();
                    $filterTo = Carbon::parse($availableTo)->endOfDay();

                    if (
                        $event
                        && (
                            $event->start_date <= $filterTo
                            && $event->end_date >= $filterFrom
                        )
                    ) {
                        $reserved += $reservation->quantity;
                    }
                }

                $item->available_quantity = max(0, $item->quantity - $reserved);
            }
        }

        return $items;
    }

    public function createItem(ItemStoreDTO $data): Item
    {
        $item = $this->repository->create($data);

        if (! empty($data->getImages())) {
            $this->imageService->uploadImages($item, $data->getImages());
        }

        if (! empty($data->getVideoFiles())) {
            $this->videoService->uploadVideos($item, $data->getVideoFiles());
        }

        $item->products()->sync($data->getProductIds());

        if ($data->getDepth() === 0) {
            $item->subitems()->sync($this->buildRelationSyncPayload($data->getSubitemsWithQuantities()));
        } else {
            $item->parentItems()->sync($this->buildRelationSyncPayload($data->getParentItemsWithQuantities()));
        }

        $this->logAction('created_item', $item);

        return $item;
    }

    public function updateItem(Item $item, ItemUpdateDTO $data): bool
    {
        $result = $this->repository->update($item, $data);

        if (! empty($data->getVideoFiles())) {
            $this->videoService->uploadVideos($item, $data->getVideoFiles());
        }

        $item->products()->sync($data->getProductIds());

        if ((int) $item->depth === 0) {
            $item->subitems()->sync($this->buildRelationSyncPayload($data->getSubitemsWithQuantities()));
        } else {
            $item->parentItems()->sync($this->buildRelationSyncPayload($data->getParentItemsWithQuantities()));
        }

        if ($result) {
            $this->logAction('updated_item', $item);
        }

        return $result;
    }

    private function buildRelationSyncPayload(array $itemsWithQuantities): array
    {
        $itemsToSync = [];

        foreach ($itemsWithQuantities as $itemId => $details) {
            if (! isset($details['selected']) || ! $details['selected']) {
                continue;
            }

            $itemsToSync[$itemId] = [
                'quantity' => isset($details['quantity']) ? max(1, (int) $details['quantity']) : 1,
            ];
        }

        return $itemsToSync;
    }

    public function deleteItem(Item $item): bool
    {
        $this->logAction('deleted_item', $item);

        return $this->repository->delete($item);
    }

    protected function logAction(string $action, Item $item): void
    {
        if (Auth::check()) {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => $action,
                'entity_type' => 'Item',
                'entity_id' => $item->id,
                'description' => "{$action}: {$item->name}",
            ]);
        }
    }
}
