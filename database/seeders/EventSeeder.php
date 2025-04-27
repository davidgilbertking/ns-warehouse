<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\Reservation;
use App\Models\User;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@local')->first();

        $futureEvent = Event::create([
                                         'name' => 'Летний фестиваль',
                                         'start_date' => now()->addDays(5),
                                         'end_date' => now()->addDays(7),
                                         'user_id' => $admin->id,
                                     ]);

        $pastEvent = Event::create([
                                       'name' => 'Зимняя конференция',
                                       'start_date' => now()->subDays(10),
                                       'end_date' => now()->subDays(8),
                                       'user_id' => $admin->id,
                                   ]);

        Reservation::create(['event_id' => $futureEvent->id, 'item_id' => 1, 'quantity' => 2]);
        Reservation::create(['event_id' => $futureEvent->id, 'item_id' => 2, 'quantity' => 4]);
        Reservation::create(['event_id' => $pastEvent->id, 'item_id' => 3, 'quantity' => 1]);
    }
}
