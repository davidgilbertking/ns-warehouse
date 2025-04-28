<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Reservation;
use App\Models\Event;

class ItemControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testIndexReturnsSuccessfulResponse(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act & Assert
        $this->actingAs($user)
             ->get(route('items.index'))
             ->assertOk()
             ->assertViewIs('items.index');
    }

    public function testCreateReturnsSuccessfulResponseForAuthorizedUser(): void
    {
        // Arrange
        $user = User::factory()->create([
                                            'role' => 'admin', // или любая роль, у которой есть права
                                        ]);

        // Act & Assert
        $this->actingAs($user)
             ->get(route('items.create'))
             ->assertOk()
             ->assertViewIs('items.create');
    }
    public function testStoreCreatesNewItem(): void
    {
        // Arrange
        $user = User::factory()->create([
                                            'role' => 'admin', // или любой не viewer
                                        ]);

        $data = [
            'name' => 'Test Item',
            'description' => 'Test Description',
            'quantity' => 5,
            'size' => 'Medium',
            'material' => 'Steel',
            'supplier' => 'Supplier A',
            'storage_location' => 'Shelf A1',
        ];

        // Act & Assert
        $this->actingAs($user)
             ->post(route('items.store'), $data)
             ->assertRedirect(route('items.index'))
             ->assertSessionHas('success', 'Предмет создан!');

        // Проверяем, что предмет действительно создан в базе
        $this->assertDatabaseHas('items', [
            'name' => 'Test Item',
            'description' => 'Test Description',
            'quantity' => 5,
        ]);
    }
    public function testShowReturnsSuccessfulResponse(): void
    {
        // Arrange
        $user = User::factory()->create([
                                            'role' => 'admin',
                                        ]);

        $item = \App\Models\Item::create([
                                             'name' => 'Test Show Item',
                                             'description' => 'Description',
                                             'quantity' => 10,
                                             'size' => 'Medium',
                                             'material' => 'Steel',
                                             'supplier' => 'Supplier X',
                                             'storage_location' => 'Shelf B1',
                                         ]);

        // Act & Assert
        $this->actingAs($user)
             ->get(route('items.show', $item))
             ->assertOk()
             ->assertViewIs('items.show')
             ->assertViewHas('item', function ($viewItem) use ($item) {
                 return $viewItem->id === $item->id;
             });
    }
    public function testEditReturnsSuccessfulResponseForAuthorizedUser(): void
    {
        // Arrange
        $user = User::factory()->create([
                                            'role' => 'admin',
                                        ]);

        $item = \App\Models\Item::create([
                                             'name' => 'Test Edit Item',
                                             'description' => 'Description',
                                             'quantity' => 7,
                                             'size' => 'Small',
                                             'material' => 'Plastic',
                                             'supplier' => 'Supplier Y',
                                             'storage_location' => 'Shelf C1',
                                         ]);

        // Act & Assert
        $this->actingAs($user)
             ->get(route('items.edit', $item))
             ->assertOk()
             ->assertViewIs('items.edit')
             ->assertViewHas('item', function ($viewItem) use ($item) {
                 return $viewItem->id === $item->id;
             });
    }
    public function testUpdateUpdatesItem(): void
    {
        // Arrange
        $user = User::factory()->create([
                                            'role' => 'admin',
                                        ]);

        $item = \App\Models\Item::create([
                                             'name' => 'Old Name',
                                             'description' => 'Old Description',
                                             'quantity' => 3,
                                             'size' => 'Small',
                                             'material' => 'Wood',
                                             'supplier' => 'Supplier Old',
                                             'storage_location' => 'Shelf D1',
                                         ]);

        $updateData = [
            'name' => 'New Name',
            'description' => 'New Description',
            'quantity' => 8,
            'size' => 'Large',
            'material' => 'Metal',
            'supplier' => 'Supplier New',
            'storage_location' => 'Shelf D2',
        ];

        // Act & Assert
        $this->actingAs($user)
             ->put(route('items.update', $item), $updateData)
             ->assertRedirect(route('items.show', $item))
             ->assertSessionHas('success', 'Предмет обновлён!');

        // Проверка что в базе данные реально обновились
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'name' => 'New Name',
            'description' => 'New Description',
            'quantity' => 8,
        ]);
    }
    public function testDestroyDeletesItem(): void
    {
        // Arrange
        $user = User::factory()->create([
                                            'role' => 'admin',
                                        ]);

        $item = \App\Models\Item::create([
                                             'name' => 'Item To Delete',
                                             'description' => 'Description',
                                             'quantity' => 5,
                                             'size' => 'Medium',
                                             'material' => 'Steel',
                                             'supplier' => 'Supplier Delete',
                                             'storage_location' => 'Shelf E1',
                                         ]);

        // Act & Assert
        $this->actingAs($user)
             ->delete(route('items.destroy', $item))
             ->assertRedirect(route('items.index'))
             ->assertSessionHas('success', 'Предмет удалён!');

        $this->assertSoftDeleted('items', [
            'id' => $item->id,
        ]);
    }

    public function testDestroyFailsIfItemHasReservationsOrProducts(): void
    {
        // Arrange
        $user = User::factory()->create([
                                            'role' => 'admin',
                                        ]);

        $item = \App\Models\Item::create([
                                             'name' => 'Protected Item',
                                             'description' => 'Cannot delete',
                                             'quantity' => 5,
                                             'size' => 'Small',
                                             'material' => 'Plastic',
                                             'supplier' => 'Supplier Protected',
                                             'storage_location' => 'Shelf F1',
                                         ]);

        $event = Event::factory()->create([
                                              'user_id' => $user->id,
                                          ]);

        Reservation::create([
                                'item_id' => $item->id,
                                'event_id' => $event->id,
                                'quantity' => 1,
                            ]);

        // Act & Assert
        $this->actingAs($user)
             ->delete(route('items.destroy', $item))
             ->assertRedirect()
             ->assertSessionHas('error', 'Нельзя удалить предмет, который зарезервирован в мероприятии.');

        $this->assertDatabaseHas('items', [
            'id' => $item->id,
        ]);
    }
    public function testExportReturnsCsvDownload(): void
    {
        // Arrange
        $user = User::factory()->create([
                                            'role' => 'admin',
                                        ]);

        \App\Models\Item::create([
                                     'name' => 'Exported Item',
                                     'description' => 'For Export',
                                     'quantity' => 15,
                                     'size' => 'Large',
                                     'material' => 'Aluminum',
                                     'supplier' => 'Supplier Export',
                                     'storage_location' => 'Shelf X1',
                                 ]);

        // Act
        $response = $this->actingAs($user)
                         ->get(route('items.export'));

        // Assert
        $response->assertOk();
        $this->assertStringStartsWith('text/csv', $response->headers->get('Content-Type'));
        $response->assertHeader('Content-Disposition');

        $this->assertStringContainsString('Exported Item', $response->getContent());
    }

}
