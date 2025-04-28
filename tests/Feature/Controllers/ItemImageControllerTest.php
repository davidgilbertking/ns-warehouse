<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers;

use App\Models\Item;
use App\Models\ItemImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ItemImageControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testStoreUploadsImageAndCreatesRecord(): void
    {
        // Arrange
        Storage::fake('public');

        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $item = Item::factory()->create();
        $file = UploadedFile::fake()->image('test-image.jpg');

        // Act
        $response = $this->post(route('items.images.store', $item), [
            'image' => $file,
        ]);

        // Assert
        $response->assertRedirect(route('items.edit', $item));
        $response->assertSessionHas('success', 'Фото загружено.');

        Storage::disk('public')->assertExists('items/' . $file->hashName());

        $this->assertDatabaseHas('item_images', [
            'item_id' => $item->id,
            'path' => 'items/' . $file->hashName(),
        ]);
    }

    public function testDestroyDeletesImageAndFile(): void
    {
        // Arrange
        Storage::fake('public');

        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $item = Item::factory()->create();
        $path = 'items/test-image.jpg';
        Storage::disk('public')->put($path, 'dummy content');

        $itemImage = ItemImage::create([
                                           'item_id' => $item->id,
                                           'path' => $path,
                                       ]);

        // Act
        $response = $this->delete(route('items.images.destroy', $itemImage));

        // Assert
        $response->assertRedirect(route('items.edit', $item));
        $response->assertSessionHas('success', 'Фото удалено.');

        Storage::disk('public')->assertMissing($path);

        $this->assertDatabaseMissing('item_images', [
            'id' => $itemImage->id,
        ]);
    }

}
