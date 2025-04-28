<?php

namespace Database\Factories;

use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'quantity' => $this->faker->numberBetween(1, 100),
            'size' => $this->faker->randomElement(['Small', 'Medium', 'Large']),
            'material' => $this->faker->word(),
            'supplier' => $this->faker->company(),
            'storage_location' => $this->faker->word(),
        ];
    }
}
