<?php

declare(strict_types=1);

namespace Tests\Unit\Services;


use App\DTOs\ItemStoreDTO;
use App\Models\Item;
use App\Repositories\ItemRepository;
use App\Services\ItemService;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\TestCase;
use Mockery;
use App\DTOs\ItemUpdateDTO;
use App\DTOs\ItemFilterDTO;

class ItemServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testCreateItemSuccessfully(): void
    {
        // Arrange
        $repositoryMock = Mockery::mock(ItemRepository::class);
        $itemService = new ItemService($repositoryMock);

        $dto = new ItemStoreDTO(
            name: 'Test Item',
            description: 'A simple test item',
            quantity: 10,
            size: 'Medium',
            material: 'Steel',
            supplier: 'Test Supplier',
            storageLocation: 'A1'
        );


        $item = new Item([
                             'id' => 1,
                             'name' => 'Test Item',
                             'quantity' => 10,
                         ]);

        $repositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($dto)
            ->andReturn($item);

        // Чтобы не писать в реальную таблицу ActivityLog
        Auth::shouldReceive('check')->andReturn(false);

        // Act
        $result = $itemService->createItem($dto);

        // Assert
        $this->assertInstanceOf(Item::class, $result);
        $this->assertEquals('Test Item', $result->name);
        $this->assertEquals(10, $result->quantity);
    }

    public function testUpdateItemSuccessfully(): void
    {
        // Arrange
        $repositoryMock = Mockery::mock(ItemRepository::class);
        $itemService = new ItemService($repositoryMock);

        $item = new Item([
                             'id' => 1,
                             'name' => 'Original Item',
                             'quantity' => 5,
                         ]);

        // Создаём реальный DTO, а не мок
        $dto = new ItemUpdateDTO(
            name: 'Updated Item',
            description: 'Updated description',
            quantity: 15,
            size: 'Large',
            material: 'Plastic',
            supplier: 'Updated Supplier',
            storageLocation: 'B2'
        );

        $repositoryMock
            ->shouldReceive('update')
            ->once()
            ->with($item, $dto)
            ->andReturn(true);

        Auth::shouldReceive('check')->andReturn(false);

        // Act
        $result = $itemService->updateItem($item, $dto);

        // Assert
        $this->assertTrue($result);
    }

    public function testDeleteItemSuccessfully(): void
    {
        // Arrange
        $repositoryMock = Mockery::mock(ItemRepository::class);
        $itemService = new ItemService($repositoryMock);

        $item = new Item([
                             'id' => 1,
                             'name' => 'Item to delete',
                             'quantity' => 0,
                         ]);

        $repositoryMock
            ->shouldReceive('delete')
            ->once()
            ->with($item)
            ->andReturn(true);

        // Защита от реальных запросов
        Auth::shouldReceive('check')->andReturn(false);

        // Act
        $result = $itemService->deleteItem($item);

        // Assert
        $this->assertTrue($result);
    }

    public function testGetPaginatedItemsCalculatesAvailableQuantityCorrectly(): void
    {
        // Arrange
        $repositoryMock = Mockery::mock(ItemRepository::class);
        $itemService = new ItemService($repositoryMock);

        $filter = new ItemFilterDTO(
            search: null,
            availableFrom: '2025-05-01',
            availableTo: '2025-05-10'
        );

        // Создаем фейковый Event
        $event = new \stdClass();
        $event->start_date = '2025-05-05';
        $event->end_date = '2025-05-06';

        // Создаем фейковую Reservation
        $reservation = new \stdClass();
        $reservation->event = $event;
        $reservation->quantity = 2;

        // Создаем фейковый Item
        $item = new Item([
                             'id' => 1,
                             'name' => 'Test Item',
                             'quantity' => 10,
                         ]);

        $item->reservations = [$reservation];

        // Пагинатор - подменим простой коллекцией через LengthAwarePaginator
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            [$item], // items
            1,       // total
            10,      // per page
            1        // current page
        );

        $repositoryMock
            ->shouldReceive('paginateWithFilters')
            ->once()
            ->with($filter, 10)
            ->andReturn($paginator);

        // Act
        $result = $itemService->getPaginatedItems($filter, 10);

        // Assert
        $this->assertEquals(1, $result->total());
        $this->assertEquals(8, $result->items()[0]->available_quantity); // 10 - 2 = 8
    }

    public function testGetPaginatedItemsIgnoresReservationOutsidePeriod(): void
    {
        // Arrange
        $repositoryMock = Mockery::mock(ItemRepository::class);
        $itemService = new ItemService($repositoryMock);

        $filter = new \App\DTOs\ItemFilterDTO(
            search: null,
            availableFrom: '2025-05-01',
            availableTo: '2025-05-10'
        );

        // Резервация ВНЕ выбранного периода (например в апреле)
        $event = new \stdClass();
        $event->start_date = '2025-04-10';
        $event->end_date = '2025-04-12';

        $reservation = new \stdClass();
        $reservation->event = $event;
        $reservation->quantity = 3;

        $item = new \App\Models\Item([
                                         'id' => 2,
                                         'name' => 'Item without reservation',
                                         'quantity' => 5,
                                     ]);

        $item->reservations = [$reservation];

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            [$item],
            1,
            10,
            1
        );

        $repositoryMock
            ->shouldReceive('paginateWithFilters')
            ->once()
            ->with($filter, 10)
            ->andReturn($paginator);

        // Act
        $result = $itemService->getPaginatedItems($filter, 10);

        // Assert
        $this->assertEquals(5, $result->items()[0]->available_quantity); // Количество не меняется
    }

    public function testGetPaginatedItemsReservationFullyCoversItem(): void
    {
        // Arrange
        $repositoryMock = Mockery::mock(ItemRepository::class);
        $itemService = new ItemService($repositoryMock);

        $filter = new \App\DTOs\ItemFilterDTO(
            search: null,
            availableFrom: '2025-05-01',
            availableTo: '2025-05-10'
        );

        // Резервация в пределах выбранного периода
        $event = new \stdClass();
        $event->start_date = '2025-05-03';
        $event->end_date = '2025-05-05';

        $reservation = new \stdClass();
        $reservation->event = $event;
        $reservation->quantity = 5;

        $item = new \App\Models\Item([
                                         'id' => 3,
                                         'name' => 'Fully Reserved Item',
                                         'quantity' => 5,
                                     ]);

        $item->reservations = [$reservation];

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            [$item],
            1,
            10,
            1
        );

        $repositoryMock
            ->shouldReceive('paginateWithFilters')
            ->once()
            ->with($filter, 10)
            ->andReturn($paginator);

        // Act
        $result = $itemService->getPaginatedItems($filter, 10);

        // Assert
        $this->assertEquals(0, $result->items()[0]->available_quantity); // Всё зарезервировано
    }

    public function testGetPaginatedItemsMultipleReservationsSumCorrectly(): void
    {
        // Arrange
        $repositoryMock = Mockery::mock(ItemRepository::class);
        $itemService = new ItemService($repositoryMock);

        $filter = new \App\DTOs\ItemFilterDTO(
            search: null,
            availableFrom: '2025-05-01',
            availableTo: '2025-05-10'
        );

        // Первая резервация
        $event1 = new \stdClass();
        $event1->start_date = '2025-05-02';
        $event1->end_date = '2025-05-04';

        $reservation1 = new \stdClass();
        $reservation1->event = $event1;
        $reservation1->quantity = 2;

        // Вторая резервация
        $event2 = new \stdClass();
        $event2->start_date = '2025-05-06';
        $event2->end_date = '2025-05-07';

        $reservation2 = new \stdClass();
        $reservation2->event = $event2;
        $reservation2->quantity = 1;

        $item = new \App\Models\Item([
                                         'id' => 4,
                                         'name' => 'Partially Reserved Item',
                                         'quantity' => 10,
                                     ]);

        $item->reservations = [$reservation1, $reservation2];

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            [$item],
            1,
            10,
            1
        );

        $repositoryMock
            ->shouldReceive('paginateWithFilters')
            ->once()
            ->with($filter, 10)
            ->andReturn($paginator);

        // Act
        $result = $itemService->getPaginatedItems($filter, 10);

        // Assert
        $this->assertEquals(7, $result->items()[0]->available_quantity); // 10 - (2+1) = 7
    }


}
