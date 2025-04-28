<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Reservation;
use App\Models\Event;

class ReservationRepository
{
    public function createReservation(Event $event, int $itemId, int $quantity): Reservation
    {
        return $event->reservations()->create([
                                                  'item_id'  => $itemId,
                                                  'quantity' => $quantity,
                                              ]);
    }

    public function deleteAllByEvent(Event $event): void
    {
        $event->reservations()->delete();
    }
}
