<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Item;
use App\Models\ItemImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_attributes(): void
    {
        $itemImage = new ItemImage;

        $this->assertEquals(
            ['item_id', 'path', 'thumb_path'],
            $itemImage->getFillable()
        );
    }

    public function test_item_relationship(): void
    {
        $item = Item::factory()->create();
        $itemImage = ItemImage::factory()->create([
            'item_id' => $item->id,
        ]);

        $this->assertInstanceOf(Item::class, $itemImage->item);
        $this->assertEquals($item->id, $itemImage->item->id);
    }
}
