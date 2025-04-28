<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\ItemRepository;
use App\Repositories\ReservationRepository;

class ItemQuantityService
{
    public function __construct(
        protected ItemRepository $itemRepository,
    ) {
    }

    public function getAvailableQuantities(string $startDate, string $endDate): array
    {
        $items = $this->itemRepository->allWithQuantities();

        $availableQuantities = [];

        foreach ($items as $item) {
            $available                      =
                $this->itemRepository->getAvailableQuantityForItem($item->id, $startDate, $endDate);
            $availableQuantities[$item->id] = $available;
        }

        return $availableQuantities;
    }

    public function checkAvailability(array $items, string $startDate, string $endDate, ?int $excludeEventId = null
    ): array {
        $availability = [];

        foreach ($items as $item) {
            $available                 = $this->itemRepository->getAvailableQuantityForItem(
                (int)$item['id'], $startDate, $endDate, $excludeEventId
            );
            $availability[$item['id']] = $available;
        }

        return $availability;
    }
}
