<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ItemImage;
use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemImageFactory extends Factory
{
    protected $model = ItemImage::class;

    public function definition(): array
    {
        return [
            'item_id' => Item::factory(), // автоматически создаст Item, если нужно
            'path'    => $this->faker->image('storage/app/public/items', 640, 480, null, false),
        ];
    }
}
