<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Item;
use App\Models\ItemImage;
use App\Repositories\ItemImageRepository;
use App\Services\ItemImageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class ItemImageServiceTest extends TestCase
{
    use RefreshDatabase;

    public function testStoreCreatesItemImage(): void
    {
        // Arrange
        $item = Item::factory()->create();
        $repository = Mockery::mock(ItemImageRepository::class);
        $service = new ItemImageService($repository);

        $repository->shouldReceive('create')
                   ->once()
                   ->withArgs(function ($itemArg, $dtoArg) {
                       return $itemArg instanceof Item && $dtoArg instanceof \App\DTOs\ItemImageDTO;
                   })
                   ->andReturn(new ItemImage(['path' => 'path/to/file.jpg']));

        // Act
        $image = $service->store($item, 'path/to/file.jpg');

        // Assert
        $this->assertInstanceOf(ItemImage::class, $image);
        $this->assertEquals('path/to/file.jpg', $image->path);
    }

    public function testDestroyDeletesImageFileAndRecord(): void
    {
        // Arrange
        Storage::fake('public');

        $item = Item::factory()->create();
        $image = ItemImage::create([
                                       'item_id' => $item->id,
                                       'path' => 'items/test_image.jpg',
                                   ]);

        Storage::disk('public')->put($image->path, 'fake content');

        $repository = Mockery::mock(ItemImageRepository::class);
        $repository->shouldReceive('delete')
                   ->once()
                   ->with($image);

        $service = new ItemImageService($repository);

        // Act
        $service->destroy($image);

        // Assert
        Storage::disk('public')->assertMissing($image->path);
    }

    public function testUploadImagesCreatesMultipleItemImages(): void
    {
        // Arrange
        Storage::fake('public');

        $item = Item::factory()->create();

        $images = [
            UploadedFile::fake()->image('image1.jpg'),
            UploadedFile::fake()->image('image2.jpg'),
        ];

        $repository = Mockery::mock(ItemImageRepository::class);
        $service = new ItemImageService($repository);

        // Act
        $service->uploadImages($item, $images);

        // Assert
        $this->assertCount(2, $item->images);
        Storage::disk('public')->assertExists($item->images()->first()->path);
    }
}
