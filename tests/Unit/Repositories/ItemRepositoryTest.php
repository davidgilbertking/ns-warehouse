<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\DTOs\ItemFilterDTO;
use App\DTOs\ItemStoreDTO;
use App\DTOs\ItemUpdateDTO;
use App\Models\Event;
use App\Models\Item;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\User;
use App\Repositories\ItemRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ItemRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_item_successfully(): void
    {
        // Arrange
        $repository = new ItemRepository;

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

    public function test_update_item_successfully(): void
    {
        // Arrange
        $repository = new ItemRepository;

        $item = Item::create([
            'name' => 'Original Item',
            'description' => 'Original Description',
            'quantity' => 5,
            'size' => 'Small',
            'material' => 'Wood',
            'supplier' => 'Original Supplier',
            'storage_location' => 'Shelf B2',
        ]);

        $dto = new ItemUpdateDTO(
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

    public function test_delete_item_successfully(): void
    {
        // Arrange
        $repository = new ItemRepository;

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

    public function test_paginate_with_filters_returns_paginator(): void
    {
        // Arrange
        $repository = new ItemRepository;

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

        $filter = new ItemFilterDTO(
            search: null,
            availableFrom: null,
            availableTo: null
        );

        // Act
        $paginator = $repository->paginateWithFilters($filter, 10);

        // Assert
        $this->assertInstanceOf(LengthAwarePaginator::class, $paginator);
        $this->assertCount(3, $paginator->items());
    }

    public function test_get_for_export_returns_collection(): void
    {
        // Arrange
        $repository = new ItemRepository;

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

        $filter = new ItemFilterDTO(
            search: null,
            availableFrom: null,
            availableTo: null
        );

        // Act
        $collection = $repository->getForExport($filter);

        // Assert
        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertCount(2, $collection);
    }

    public function test_paginate_with_filters_search_is_case_insensitive_for_latin_and_cyrillic(): void
    {
        $repository = new ItemRepository;
        $latinItem = Item::factory()->create([
            'depth' => 1,
            'name' => 'ABC Cube',
        ]);
        $cyrillicItem = Item::factory()->create([
            'depth' => 1,
            'description' => 'Большой АБВ куб',
        ]);
        Item::factory()->create([
            'depth' => 1,
            'name' => 'Other Item',
            'description' => 'Other description',
        ]);

        $latinResults = $repository->paginateWithFilters(new ItemFilterDTO(search: 'abc'), 10, 1);
        $cyrillicResults = $repository->paginateWithFilters(new ItemFilterDTO(search: 'абв'), 10, 1);

        $this->assertTrue(collect($latinResults->items())->contains('id', $latinItem->id));
        $this->assertTrue(collect($cyrillicResults->items())->contains('id', $cyrillicItem->id));
        $this->assertSame(1, $latinResults->total());
        $this->assertSame(1, $cyrillicResults->total());
    }

    public function test_product_and_storage_place_filters_are_case_insensitive_for_latin_and_cyrillic(): void
    {
        $repository = new ItemRepository;
        $product = Product::factory()->create(['name' => 'Тэг ABC АБВ']);
        $productItem = Item::factory()->create([
            'depth' => 1,
            'name' => 'Item with tag',
        ]);
        $storageItem = Item::factory()->create([
            'depth' => 1,
            'name' => 'Item in storage',
            'storage_place' => 'СКЛАД ABC АБВ',
        ]);

        $productItem->products()->attach($product->id, ['quantity' => 1]);

        $productResults = $repository->paginateWithFilters(new ItemFilterDTO(product: 'абв'), 10, 1);
        $storageResults = $repository->paginateWithFilters(new ItemFilterDTO(storagePlace: 'склад abc'), 10, 1);

        $this->assertTrue(collect($productResults->items())->contains('id', $productItem->id));
        $this->assertTrue(collect($storageResults->items())->contains('id', $storageItem->id));
        $this->assertSame(1, $productResults->total());
        $this->assertSame(1, $storageResults->total());
    }

    public function test_all_with_quantities_returns_correct_fields(): void
    {
        // Arrange
        $repository = new ItemRepository;

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
        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertCount(1, $collection);

        /** @var Item $item */
        $item = $collection->first();

        $this->assertArrayHasKey('id', $item->getAttributes());
        $this->assertArrayHasKey('name', $item->getAttributes());
        $this->assertArrayHasKey('quantity', $item->getAttributes());

        // Убедимся, что описание и прочие поля отсутствуют
        $this->assertArrayNotHasKey('description', $item->getAttributes());
        $this->assertArrayNotHasKey('size', $item->getAttributes());
        $this->assertArrayNotHasKey('material', $item->getAttributes());
    }

    public function test_find_with_relations_returns_item_with_relations(): void
    {
        // Arrange
        $repository = new ItemRepository;

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

    public function test_paginate_with_filters_loads_parent_items_relation(): void
    {
        $repository = new ItemRepository;
        Item::factory()->create(['depth' => 1]);

        $paginator = $repository->paginateWithFilters(new ItemFilterDTO, 10, 1);
        $item = collect($paginator->items())->first();

        $this->assertInstanceOf(Item::class, $item);
        $this->assertTrue($item->relationLoaded('parentItems'));
    }

    public function test_get_available_quantity_for_item_calculates_correctly(): void
    {
        // Arrange
        $repository = new ItemRepository;

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
