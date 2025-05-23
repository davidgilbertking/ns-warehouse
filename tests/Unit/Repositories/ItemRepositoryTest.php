<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\DTOs\ItemStoreDTO;
use App\Models\Item;
use App\Repositories\ItemRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Reservation;
use App\Models\Event;
use App\Models\User;

class ItemRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function testCreateItemSuccessfully(): void
    {
        // Arrange
        $repository = new ItemRepository();

        $dto = new ItemStoreDTO(
            name: 'New Test Item',
            description: 'Test Description',
            quantity: 10,
            size: 'Medium',
            material: 'Plastic',
            supplier: 'Test Supplier',
            storageLocation: 'Shelf A1',
            mechanics: null,
            scalability: null,
            clientPrice: null,
            brandingOptions: null,
            adaptationOptions: null,
            opPrice: null,
            constructionDescription: null,
            contractor: null,
            productionCost: null,
            changeHistory: null,
            consumables: null,
            implementationComments: null,
            mounting: null,
            storageFeatures: null,
            designLinks: null,
            eventHistory: null,
            storagePlace: null,
            images: null,
            opMedia: [],
            realMedia: [],
            eventMedia: [],
        );

        // Act
        $item = $repository->create($dto);

        // Assert
        $this->assertInstanceOf(Item::class, $item);
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'name' => 'New Test Item',
            'description' => 'Test Description',
            'quantity' => 10,
            'size' => 'Medium',
            'material' => 'Plastic',
            'supplier' => 'Test Supplier',
            'storage_location' => 'Shelf A1',
        ]);
    }

    public function testUpdateItemSuccessfully(): void
    {
        // Arrange
        $repository = new ItemRepository();

        $item = Item::create([
                                 'name' => 'Original Item',
                                 'description' => 'Original Description',
                                 'quantity' => 5,
                                 'size' => 'Small',
                                 'material' => 'Wood',
                                 'supplier' => 'Original Supplier',
                                 'storage_location' => 'Shelf B2',
                             ]);

        $dto = new \App\DTOs\ItemUpdateDTO(
            name: 'Updated Item',
            description: 'Updated Description',
            quantity: 15,
            size: 'Large',
            material: 'Metal',
            supplier: 'Updated Supplier',
            storageLocation: 'Shelf C3',
            mechanics: null,
            scalability: null,
            clientPrice: null,
            brandingOptions: null,
            adaptationOptions: null,
            opPrice: null,
            constructionDescription: null,
            contractor: null,
            productionCost: null,
            changeHistory: null,
            consumables: null,
            implementationComments: null,
            mounting: null,
            storageFeatures: null,
            designLinks: null,
            eventHistory: null,
            storagePlace: null,
            opMedia: [],
            realMedia: [],
            eventMedia: [],
        );

        // Act
        $result = $repository->update($item, $dto);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'name' => 'Updated Item',
            'description' => 'Updated Description',
            'quantity' => 15,
            'size' => 'Large',
            'material' => 'Metal',
            'supplier' => 'Updated Supplier',
            'storage_location' => 'Shelf C3',
        ]);
    }

    public function testDeleteItemSuccessfully(): void
    {
        // Arrange
        $repository = new ItemRepository();

        $item = Item::create([
                                 'name' => 'Item To Delete',
                                 'description' => 'Description',
                                 'quantity' => 7,
                                 'size' => 'Small',
                                 'material' => 'Steel',
                                 'supplier' => 'Supplier Inc.',
                                 'storage_location' => 'Shelf D1',
                             ]);

        // Act
        $result = $repository->delete($item);

        // Assert
        $this->assertTrue($result);

        $this->assertSoftDeleted('items', [
            'id' => $item->id,
        ]);
    }

    public function testPaginateWithFiltersReturnsPaginator(): void
    {
        // Arrange
        $repository = new ItemRepository();

        // Создаем 3 предмета
        Item::create([
                         'name' => 'Hammer',
                         'description' => 'A useful tool',
                         'quantity' => 5,
                         'size' => 'Medium',
                         'material' => 'Steel',
                         'supplier' => 'Supplier One',
                         'storage_location' => 'Shelf A1',
                     ]);

        Item::create([
                         'name' => 'Screwdriver',
                         'description' => 'Flathead',
                         'quantity' => 8,
                         'size' => 'Small',
                         'material' => 'Iron',
                         'supplier' => 'Supplier Two',
                         'storage_location' => 'Shelf A2',
                     ]);

        Item::create([
                         'name' => 'Wrench',
                         'description' => 'Adjustable',
                         'quantity' => 12,
                         'size' => 'Large',
                         'material' => 'Steel',
                         'supplier' => 'Supplier Three',
                         'storage_location' => 'Shelf A3',
                     ]);

        // ⛔️ Без поиска, чтобы избежать ILIKE
        $filter = new \App\DTOs\ItemFilterDTO(
            search: null,
            availableFrom: null,
            availableTo: null
        );

        // Act
        $paginator = $repository->paginateWithFilters($filter, 10);

        // Assert
        $this->assertInstanceOf(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class, $paginator);
        $this->assertCount(3, $paginator->items());
    }

    public function testGetForExportReturnsCollection(): void
    {
        // Arrange
        $repository = new ItemRepository();

        Item::create([
                         'name' => 'Hammer',
                         'description' => 'A useful tool',
                         'quantity' => 5,
                         'size' => 'Medium',
                         'material' => 'Steel',
                         'supplier' => 'Supplier One',
                         'storage_location' => 'Shelf A1',
                     ]);

        Item::create([
                         'name' => 'Screwdriver',
                         'description' => 'Flathead',
                         'quantity' => 8,
                         'size' => 'Small',
                         'material' => 'Iron',
                         'supplier' => 'Supplier Two',
                         'storage_location' => 'Shelf A2',
                     ]);

        // ⛔️ Без поиска
        $filter = new \App\DTOs\ItemFilterDTO(
            search: null,
            availableFrom: null,
            availableTo: null
        );

        // Act
        $collection = $repository->getForExport($filter);

        // Assert
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $collection);
        $this->assertCount(2, $collection);
    }

    public function testAllWithQuantitiesReturnsCorrectFields(): void
    {
        // Arrange
        $repository = new ItemRepository();

        Item::create([
                         'name' => 'Test Item',
                         'description' => 'Test Description',
                         'quantity' => 5,
                         'size' => 'Medium',
                         'material' => 'Steel',
                         'supplier' => 'Supplier One',
                         'storage_location' => 'Shelf A1',
                     ]);

        // Act
        $collection = $repository->allWithQuantities();

        // Assert
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $collection);
        $this->assertCount(1, $collection);

        /** @var \App\Models\Item $item */
        $item = $collection->first();

        $this->assertArrayHasKey('id', $item->getAttributes());
        $this->assertArrayHasKey('name', $item->getAttributes());
        $this->assertArrayHasKey('quantity', $item->getAttributes());

        // Убедимся, что описание и прочие поля отсутствуют
        $this->assertArrayNotHasKey('description', $item->getAttributes());
        $this->assertArrayNotHasKey('size', $item->getAttributes());
        $this->assertArrayNotHasKey('material', $item->getAttributes());
    }

    public function testFindWithRelationsReturnsItemWithRelations(): void
    {
        // Arrange
        $repository = new ItemRepository();

        $item = Item::create([
                                 'name' => 'Item With Relations',
                                 'description' => 'Test Description',
                                 'quantity' => 10,
                                 'size' => 'Large',
                                 'material' => 'Aluminum',
                                 'supplier' => 'Supplier X',
                                 'storage_location' => 'Shelf B1',
                             ]);

        // Act
        $foundItem = $repository->findWithRelations($item->id);

        // Assert
        $this->assertInstanceOf(Item::class, $foundItem);
        $this->assertEquals($item->id, $foundItem->id);

        // Проверяем, что связи загружены (даже если пустые)
        $this->assertTrue($foundItem->relationLoaded('products'));
        $this->assertTrue($foundItem->relationLoaded('reservations'));
    }

    public function testGetAvailableQuantityForItemCalculatesCorrectly(): void
    {
        // Arrange
        $repository = new ItemRepository();

        $user = User::create([
                                 'name' => 'Test User',
                                 'email' => 'test@example.com',
                                 'password' => bcrypt('password'), // или Hash::make, неважно для теста
                             ]);

        $item = Item::create([
                                 'name' => 'Reserved Item',
                                 'description' => 'Reserved Description',
                                 'quantity' => 10,
                                 'size' => 'Medium',
                                 'material' => 'Plastic',
                                 'supplier' => 'Supplier Y',
                                 'storage_location' => 'Shelf Z1',
                             ]);

        $event = Event::create([
                                   'name' => 'Test Event',
                                   'start_date' => '2025-05-03',
                                   'end_date' => '2025-05-05',
                                   'user_id' => $user->id, // Важно! Используем реального пользователя
                               ]);

        Reservation::create([
                                'item_id' => $item->id,
                                'event_id' => $event->id,
                                'quantity' => 3,
                            ]);

        // Act
        $availableQuantity = $repository->getAvailableQuantityForItem(
            itemId: $item->id,
            startDate: '2025-05-01',
            endDate: '2025-05-10'
        );

        // Assert
        $this->assertEquals(7, $availableQuantity); // 10 - 3 = 7
    }
}
