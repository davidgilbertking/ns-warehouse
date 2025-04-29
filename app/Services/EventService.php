<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Event;
use App\Repositories\EventRepository;
use App\Repositories\ReservationRepository;
use App\Repositories\ProductRepository;
use App\Repositories\ItemRepository;
use App\DTOs\EventStoreDTO;
use App\DTOs\EventUpdateDTO;
use App\Models\ActivityLog;
use App\DTOs\EventCreateInputDTO;
use Illuminate\Validation\ValidationException;

class EventService
{
    public function __construct(
        protected EventRepository       $eventRepository,
        protected ReservationRepository $reservationRepository,
        protected ProductRepository     $productRepository,
        protected ItemRepository        $itemRepository,
    ) {
    }

    public function getFilteredEvents(array $filters)
    {
        return $this->eventRepository->getFilteredEvents($filters);
    }

    public function getCreateData(EventCreateInputDTO $dto): array
    {
        $items    = $this->itemRepository->allWithQuantities();
        $products = $this->productRepository->all();

        $availableQuantities = [];

        foreach ($items as $item) {
            $availableQuantities[$item->id] = $this->itemRepository->getAvailableQuantityForItem(
                $item->id, now()->toDateString(), now()->toDateString()
            );
        }

        $preselectedItems = [];

        if ($dto->productId !== null) {
            $product = $this->productRepository->findWithItems($dto->productId);

            if ($product) {
                foreach ($product->items as $item) {
                    $preselectedItems[] = [
                        'id'       => $item->id,
                        'name'     => $item->name,
                        'quantity' => 1,
                    ];
                }
            }
        }

        return compact('items', 'products', 'availableQuantities', 'preselectedItems');
    }

    public function loadEventWithRelations(Event $event): Event
    {
        return $this->eventRepository->loadEventWithRelations($event);
    }

    public function getEditData(Event $event): array
    {
        $items    = $this->itemRepository->allWithQuantities();
        $products = $this->productRepository->all();

        $availableQuantities = [];

        foreach ($items as $item) {
            $availableQuantities[$item->id] = $this->itemRepository->getAvailableQuantityForItem(
                $item->id, now()->toDateString(), now()->toDateString(), $event->id
            );
        }

        $preselectedItems = [];

        foreach ($event->reservations as $reservation) {
            $preselectedItems[] = [
                'id'       => $reservation->item_id,
                'name'     => $reservation->item->name,
                'quantity' => $reservation->quantity,
            ];
        }

        return compact('event', 'items', 'products', 'availableQuantities', 'preselectedItems');
    }

    public function getCloneData(Event $event): array
    {
        $data = $this->getCreateData(new EventCreateInputDTO());

        $defaultName      = $event->name . ' (копия)';
        $preselectedItems = [];

        foreach ($event->reservations as $reservation) {
            $preselectedItems[] = [
                'id'       => $reservation->item_id,
                'name'     => $reservation->item->name,
                'quantity' => $reservation->quantity,
            ];
        }

        $data['preselectedItems'] = $preselectedItems;
        $data['defaultName']      = $defaultName;

        return $data;
    }

    public function createEvent(EventStoreDTO $dto): Event
    {
        if (!empty($dto->items)) {
            $this->ensureItemsAreAvailable($dto->items, $dto->startDate, $dto->endDate);
        }

        $event = $this->eventRepository->create($dto);

        foreach ($dto->items ?? [] as $item) {
            $this->reservationRepository->createReservation($event, (int)$item['id'], (int)$item['quantity']);
        }

        $this->logAction('created_event', $event);

        return $event;
    }

    public function updateEvent(Event $event, EventUpdateDTO $dto): void
    {
        if (!empty($dto->items)) {
            $this->ensureItemsAreAvailable($dto->items, $dto->startDate, $dto->endDate, $event->id);
        }

        $this->eventRepository->update($event, $dto);
        $this->reservationRepository->deleteAllByEvent($event);

        foreach ($dto->items ?? [] as $item) {
            $this->reservationRepository->createReservation($event, (int)$item['id'], (int)$item['quantity']);
        }

        $this->logAction('updated_event', $event);
    }

    public function deleteEvent(Event $event): void
    {
        $this->eventRepository->delete($event);

        $this->logAction('deleted_event', $event);
    }

    private function logAction(string $action, Event $event): void
    {
        if (auth()->check()) {
            ActivityLog::create([
                                    'user_id'     => auth()->id(),
                                    'action'      => $action,
                                    'entity_type' => 'Event',
                                    'entity_id'   => $event->id,
                                    'description' => ucfirst(str_replace('_', ' ', $action)) . ": {$event->name}",
                                ]);
        }
    }

    private function ensureItemsAreAvailable(
        array $items, string $startDate, string $endDate, ?int $excludeEventId = null
    ): void {
        foreach ($items as $item) {
            $itemId       = (int)$item['id'];
            $requestedQty = (int)$item['quantity'];

            $available =
                $this->itemRepository->getAvailableQuantityForItem($itemId, $startDate, $endDate, $excludeEventId);

            if ($requestedQty > $available) {
                $name = optional($this->itemRepository->find($itemId))->name ?? "ID $itemId";
                throw ValidationException::withMessages([
                                                            'items' => "Недостаточно доступных единиц для предмета \"{$name}\" (доступно: {$available})",
                                                        ]);
            }
        }
    }
}
