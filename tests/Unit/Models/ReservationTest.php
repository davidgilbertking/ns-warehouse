<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Event;
use App\Models\Item;
use App\Models\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationTest extends TestCase
{
    use RefreshDatabase;

    public function testReservationCanBeCreated(): void
    {
        // Arrange
        $event = Event::factory()->create();
        $item = Item::factory()->create();

        // Act
        $reservation = Reservation::factory()->create([
                                                          'event_id' => $event->id,
                                                          'item_id' => $item->id,
                                                          'quantity' => 5,
                                                      ]);

        // Assert
        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'event_id' => $event->id,
            'item_id' => $item->id,
            'quantity' => 5,
        ]);
    }

    public function testReservationBelongsToEvent(): void
    {
        // Arrange
        $reservation = Reservation::factory()->create();

        // Act & Assert
        $this->assertInstanceOf(Event::class, $reservation->event);
        $this->assertNotNull($reservation->event);
    }

    public function testReservationBelongsToItem(): void
    {
        // Arrange
        $reservation = Reservation::factory()->create();

        // Act & Assert
        $this->assertInstanceOf(Item::class, $reservation->item);
        $this->assertNotNull($reservation->item);
    }
}
