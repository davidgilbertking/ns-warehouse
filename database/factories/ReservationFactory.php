<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Event;
use App\Models\Item;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReservationFactory extends Factory
{
    protected $model = Reservation::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'item_id' => Item::factory(),
            'quantity' => $this->faker->numberBetween(1, 10),
        ];
    }
}
