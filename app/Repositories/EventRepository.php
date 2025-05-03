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
        // Проверка валидности диапазона
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            if ($filters['start_date'] > $filters['end_date']) {
                throw new \InvalidArgumentException('Дата "От" не может быть позже даты "До".');
            }
        }

        $query = Event::query();

        if (!empty($filters['search'])) {
            $query->where('name', 'ILIKE', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            // Ищем любое пересечение
            $query->where(function ($q) use ($filters) {
                $q->whereDate('start_date', '<=', $filters['end_date'])
                  ->whereDate('end_date', '>=', $filters['start_date']);
            });
        } elseif (!empty($filters['start_date'])) {
            // Только start_date задан — пересечение по этому дню
            $query->whereDate('end_date', '>=', $filters['start_date']);
        } elseif (!empty($filters['end_date'])) {
            // Только end_date задан — пересечение по этому дню
            $query->whereDate('start_date', '<=', $filters['end_date']);
        }

        if (empty($filters['show_archive']) || $filters['show_archive'] == false) {
            $query->where('end_date', '>=', now()->startOfDay());
        }

        return $query->orderBy('start_date', 'desc')->paginate(10)->withQueryString();
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
