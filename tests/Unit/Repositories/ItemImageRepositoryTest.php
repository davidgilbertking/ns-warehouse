<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Models\Item;
use App\Models\ItemImage;
use App\Repositories\ItemImageRepository;
use App\DTOs\ItemImageDTO;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemImageRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function testCreateStoresImage(): void
    {
        // Arrange
        $repository = new ItemImageRepository();

        $item = Item::factory()->create();

        $dto = new ItemImageDTO('items/test-image.jpg');

        // Act
        $itemImage = $repository->create($item, $dto);

        // Assert
        $this->assertInstanceOf(ItemImage::class, $itemImage);
        $this->assertDatabaseHas('item_images', [
            'id' => $itemImage->id,
            'item_id' => $item->id,
            'path' => 'items/test-image.jpg',
        ]);
    }

    public function testDeleteRemovesImage(): void
    {
        // Arrange
        $repository = new ItemImageRepository();

        $item = Item::factory()->create();

        $itemImage = ItemImage::create([
                                           'item_id' => $item->id,
                                           'path' => 'items/test-delete-image.jpg',
                                       ]);

        // Act
        $result = $repository->delete($itemImage);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseMissing('item_images', [
            'id' => $itemImage->id,
        ]);
    }
}
