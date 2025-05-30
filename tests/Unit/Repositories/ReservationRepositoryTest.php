<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Models\Event;
use App\Models\Item;
use App\Models\Reservation;
use App\Repositories\ReservationRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected ReservationRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ReservationRepository();
    }

    public function testCreateReservationSuccessfully(): void
    {
        $event = Event::factory()->create();
        $item = Item::factory()->create();

        $reservation = $this->repository->createReservation($event, $item->id, 3);

        $this->assertInstanceOf(Reservation::class, $reservation);
        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'event_id' => $event->id,
            'item_id' => $item->id,
            'quantity' => 3,
        ]);
    }

    public function testDeleteAllByEventSuccessfully(): void
    {
        $event = Event::factory()->create();
        $item1 = Item::factory()->create();
        $item2 = Item::factory()->create();

        $reservation1 = $this->repository->createReservation($event, $item1->id, 2);
        $reservation2 = $this->repository->createReservation($event, $item2->id, 4);

        $this->assertDatabaseCount('reservations', 2);

        $this->repository->deleteAllByEvent($event);

        $this->assertSoftDeleted('reservations', ['id' => $reservation1->id]);
        $this->assertSoftDeleted('reservations', ['id' => $reservation2->id]);
    }
}
