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

        $this->eventRepository = Mockery::mock(EventRepository::class);
        $this->reservationRepository = Mockery::mock(ReservationRepository::class);
        $this->productRepository = Mockery::mock(ProductRepository::class);
        $this->itemRepository = Mockery::mock(ItemRepository::class);

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
            name: 'Test Event',
            startDate: now()->format('Y-m-d H:i:s'),
            endDate: now()->addDays(2)->format('Y-m-d H:i:s')
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
            name: 'Updated Event',
            startDate: now()->format('Y-m-d H:i:s'),
            endDate: now()->addDays(3)->format('Y-m-d H:i:s')
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
}
