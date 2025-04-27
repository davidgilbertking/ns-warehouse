<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $picnic = Product::create([
                                      'name' => 'Пакет для пикника',
                                  ]);

        $conference = Product::create([
                                          'name' => 'Пакет для конференции',
                                      ]);

        $picnic->items()->attach([
                                     1 => ['quantity' => 2], // 2 стола
                                     2 => ['quantity' => 6], // 6 стульев
                                     5 => ['quantity' => 1], // 1 шатёр
                                 ]);

        $conference->items()->attach([
                                         1 => ['quantity' => 5], // 5 столов
                                         2 => ['quantity' => 10], // 10 стульев
                                         3 => ['quantity' => 1], // 1 проектор
                                         4 => ['quantity' => 2], // 2 колонки
                                     ]);
    }
}
