<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        Item::create([
                         'name' => 'Стол',
                         'description' => 'Деревянный складной стол',
                         'size' => '120x60 см',
                         'material' => 'Дерево',
                         'supplier' => 'МебельСтрой',
                         'storage_location' => 'Склад 1',
                         'quantity' => 10,
                     ]);

        Item::create([
                         'name' => 'Стул',
                         'description' => 'Пластиковый стул',
                         'size' => '45x45x90 см',
                         'material' => 'Пластик',
                         'supplier' => 'КомфортЛайн',
                         'storage_location' => 'Склад 2',
                         'quantity' => 20,
                     ]);

        Item::create([
                         'name' => 'Проектор',
                         'description' => 'Full HD проектор для мероприятий',
                         'size' => '30x20x10 см',
                         'material' => 'Пластик и стекло',
                         'supplier' => 'ТехноМир',
                         'storage_location' => 'Офис',
                         'quantity' => 2,
                     ]);

        Item::create([
                         'name' => 'Колонка',
                         'description' => 'Портативная акустическая система',
                         'size' => '50x30x30 см',
                         'material' => 'Пластик',
                         'supplier' => 'ЗвукПрофи',
                         'storage_location' => 'Склад 3',
                         'quantity' => 4,
                     ]);

        Item::create([
                         'name' => 'Шатёр',
                         'description' => 'Большой шатёр для мероприятий',
                         'size' => '3x3 метра',
                         'material' => 'Полиэстер и алюминий',
                         'supplier' => 'УютМаркет',
                         'storage_location' => 'Уличный склад',
                         'quantity' => 3,
                     ]);
    }
}
