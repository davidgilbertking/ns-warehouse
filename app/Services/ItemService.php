<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\ItemStoreDTO;
use App\DTOs\ItemUpdateDTO;
use App\Repositories\ItemRepository;
use App\DTOs\ItemFilterDTO;
use App\Models\Item;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ItemService
{
    public function __construct(
        protected ItemRepository $repository,
        protected ItemImageService $imageService
    ) {}

    public function getPaginatedItems(ItemFilterDTO $filter, int $perPage = 10): LengthAwarePaginator
    {
        $items = $this->repository->paginateWithFilters($filter, $perPage);

        if ($filter->getAvailableFrom() && $filter->getAvailableTo()) {
            foreach ($items as $item) {
                $reserved = 0;

                foreach ($item->reservations as $reservation) {
                    $event = $reservation->event;
                    if ($event && (
                            ($event->start_date >= $filter->getAvailableFrom() && $event->start_date <= $filter->getAvailableTo()) ||
                            ($event->end_date >= $filter->getAvailableFrom() && $event->end_date <= $filter->getAvailableTo()) ||
                            ($event->start_date <= $filter->getAvailableFrom() && $event->end_date >= $filter->getAvailableTo())
                        )) {
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

        // Загружаем изображения, если есть
        if (!empty($data->images)) {
            $this->imageService->uploadImages($item, $data->images);
        }

        $this->logAction('created_item', $item);

        return $item;
    }

    public function updateItem(Item $item, ItemUpdateDTO $data): bool
    {
        $result = $this->repository->update($item, $data);

        if ($result) {
            $this->logAction('updated_item', $item);
        }

        return $result;
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
