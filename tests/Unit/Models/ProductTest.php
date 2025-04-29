<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Item;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function testFillableProperties(): void
    {
        $product = new Product();

        $this->assertEquals([
                                'name',
                            ], $product->getFillable());
    }

    public function testItemsRelation(): void
    {
        $product = Product::factory()->create();
        $item = Item::factory()->create();

        $product->items()->attach($item->id, ['quantity' => 5]);

        $this->assertTrue($product->items->contains($item));

        $pivotQuantity = $product->items->first()->pivot->quantity;
        $this->assertEquals(5, $pivotQuantity);
    }
}
