<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\EventService;
use App\Repositories\EventRepository;
use App\Repositories\ReservationRepository;
use App\Repositories\ProductRepository;
use App\Repositories\ItemRepository;
use App\Models\Event;
use App\DTOs\EventStoreDTO;
use App\DTOs\EventUpdateDTO;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use App\Models\Item;

class EventServiceTest extends TestCase
{
    use RefreshDatabase;

    protected EventRepository $eventRepository;

    protected ReservationRepository $reservationRepository;

    protected ProductRepository $productRepository;

    protected ItemRepository $itemRepository;

    protected EventService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->eventRepository       = Mockery::mock(EventRepository::class);
        $this->reservationRepository = Mockery::mock(ReservationRepository::class);
        $this->productRepository     = Mockery::mock(ProductRepository::class);
        $this->itemRepository        = Mockery::mock(ItemRepository::class);

        $this->service = new EventService(
            $this->eventRepository,
            $this->reservationRepository,
            $this->productRepository,
            $this->itemRepository
        );
    }

    public function testCreateEventSuccessfully(): void
    {
        $dto = new EventStoreDTO(
            name:      'Test Event',
            startDate: now()->format('Y-m-d H:i:s'),
            endDate:   now()->addDays(2)->format('Y-m-d H:i:s')
        );

        $event = Event::factory()->make();

        $this->eventRepository->shouldReceive('create')
                              ->once()
                              ->with($dto)
                              ->andReturn($event);

        $this->reservationRepository->shouldReceive('createReservation')
                                    ->zeroOrMoreTimes();

        $result = $this->service->createEvent($dto);

        $this->assertSame($event, $result);
    }

    public function testUpdateEventSuccessfully(): void
    {
        $event = Event::factory()->create();

        $dto = new EventUpdateDTO(
            name:      'Updated Event',
            startDate: now()->format('Y-m-d H:i:s'),
            endDate:   now()->addDays(3)->format('Y-m-d H:i:s')
        );

        $this->eventRepository->shouldReceive('update')
                              ->once()
                              ->with($event, $dto)
                              ->andReturn(true);

        $this->reservationRepository->shouldReceive('deleteAllByEvent')
                                    ->once()
                                    ->with($event);

        $this->reservationRepository->shouldReceive('createReservation')
                                    ->zeroOrMoreTimes();

        $this->service->updateEvent($event, $dto);

        $this->assertTrue(true); // если дошли сюда без ошибок — всё хорошо
    }

    public function testDeleteEventSuccessfully(): void
    {
        $event = Event::factory()->create();

        $this->eventRepository->shouldReceive('delete')
                              ->once()
                              ->with($event)
                              ->andReturn(true);

        $this->service->deleteEvent($event);

        $this->assertTrue(true);
    }

    public function testLoadEventWithRelationsSuccessfully(): void
    {
        $event = Event::factory()->create();

        $this->eventRepository->shouldReceive('loadEventWithRelations')
                              ->once()
                              ->with($event)
                              ->andReturn($event);

        $result = $this->service->loadEventWithRelations($event);

        $this->assertSame($event, $result);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testCreateEventFailsWhenItemUnavailable(): void
    {
        $dto = new EventStoreDTO(
            name:      'Unavailable Event',
            startDate: now()->format('Y-m-d H:i:s'),
            endDate:   now()->addDays(2)->format('Y-m-d H:i:s'),
            items:     [
                           ['id' => 1, 'quantity' => 10],
                       ],
        );

        $this->itemRepository->shouldReceive('getAvailableQuantityForItem')
                             ->once()
                             ->with(1, Mockery::any(), Mockery::any(), null)
                             ->andReturn(5);

        $this->itemRepository->shouldReceive('find')
                             ->once()
                             ->with(1)
                             ->andReturn(Item::factory()->make(['name' => 'Test Item']));

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->expectExceptionMessage('Недостаточно доступных единиц для предмета "Test Item" (доступно: 5)');

        $this->service->createEvent($dto);
    }

    public function testUpdateEventFailsWhenItemUnavailable(): void
    {
        $event = Event::factory()->create();

        $dto = new EventUpdateDTO(
            name:      'Unavailable Update Event',
            startDate: now()->format('Y-m-d H:i:s'),
            endDate:   now()->addDays(2)->format('Y-m-d H:i:s'),
            items:     [
                           ['id' => 2, 'quantity' => 15],
                       ],
        );

        $this->itemRepository->shouldReceive('getAvailableQuantityForItem')
                             ->once()
                             ->with(2, Mockery::any(), Mockery::any(), $event->id)
                             ->andReturn(8);

        $this->itemRepository->shouldReceive('find')
                             ->once()
                             ->with(2)
                             ->andReturn(Item::factory()->make(['name' => 'Second Test Item']));

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->expectExceptionMessage('Недостаточно доступных единиц для предмета "Second Test Item" (доступно: 8)');

        $this->service->updateEvent($event, $dto);
    }
}
