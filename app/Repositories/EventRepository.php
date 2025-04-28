<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Event;
use App\DTOs\EventStoreDTO;
use App\DTOs\EventUpdateDTO;

class EventRepository
{

    public function getFilteredEvents(array $filters)
    {
        $query = Event::query();

        if (!empty($filters['search'])) {
            $query->where('name', 'ILIKE', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('start_date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('end_date', '<=', $filters['end_date']);
        }

        if (empty($filters['show_archive']) || $filters['show_archive'] == false) {
            $query->where('end_date', '>=', now()->startOfDay());
        }

        return $query->orderBy('start_date')->paginate(10)->withQueryString();
    }

    public function loadEventWithRelations(Event $event): Event
    {
        return $event->load('reservations.item', 'user');
    }

    public function create(EventStoreDTO $dto): Event
    {
        return Event::create([
                                 'name' => $dto->name,
                                 'start_date' => $dto->startDate,
                                 'end_date' => $dto->endDate,
                                 'user_id' => auth()->id(),
                             ]);
    }

    public function update(Event $event, EventUpdateDTO $dto): bool
    {
        return $event->update([
                                  'name' => $dto->name,
                                  'start_date' => $dto->startDate,
                                  'end_date' => $dto->endDate,
                              ]);
    }

    public function delete(Event $event): bool
    {
        return $event->delete();
    }
}
