<?php

namespace Database\Factories;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActivityLogFactory extends Factory
{
    protected $model = ActivityLog::class;

    public function definition(): array
    {
        return [
            'user_id'     => User::factory(),
            'action'      => $this->faker->randomElement(['created_item', 'deleted_event', 'updated_product']),
            'entity_type' => $this->faker->randomElement(['Item', 'Event', 'Product']),
            'entity_id'   => $this->faker->numberBetween(1, 100),
            'description' => $this->faker->sentence,
            'created_at'  => now(),
            'updated_at'  => now(),
        ];
    }
}
