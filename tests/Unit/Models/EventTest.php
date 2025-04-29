<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Event;
use App\Models\Reservation;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventTest extends TestCase
{
    use RefreshDatabase;

    public function testFillableFields(): void
    {
        $event = new Event();

        $this->assertEquals([
                                'name',
                                'start_date',
                                'end_date',
                                'user_id',
                            ], $event->getFillable());
    }

    public function testReservationsRelation(): void
    {
        $event = Event::factory()->create();
        $reservation = Reservation::factory()->create(['event_id' => $event->id]);

        $this->assertTrue($event->reservations->contains($reservation));
    }



    public function testUserRelation(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create(['user_id' => $user->id]);

        $this->assertEquals($user->id, $event->user->id);
    }
}
