<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\DTOs\EventStoreDTO;
use App\DTOs\EventUpdateDTO;
use App\Models\Event;
use App\Models\User;
use App\Repositories\EventRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function testCreateEventSuccessfully(): void
    {
        $this->actingAs(User::factory()->create());

        $repository = new EventRepository();

        $dto = new EventStoreDTO(
            name: 'Test Event',
            startDate: now()->addDays(1)->toDateString(), // ✅ теперь строка
            endDate: now()->addDays(3)->toDateString()
        );

        $event = $repository->create($dto);

        $this->assertInstanceOf(Event::class, $event);
        $this->assertEquals('Test Event', $event->name);
        $this->assertEquals(auth()->id(), $event->user_id);
    }

    public function testUpdateEventSuccessfully(): void
    {
        $this->actingAs(User::factory()->create());

        $event = Event::factory()->create([
                                              'name' => 'Old Name',
                                              'start_date' => now()->addDays(1)->toDateString(),
                                              'end_date' => now()->addDays(2)->toDateString(),
                                              'user_id' => auth()->id(),
                                          ]);

        $repository = new EventRepository();

        $dto = new EventUpdateDTO(
            name: 'Updated Event',
            startDate: now()->addDays(5)->toDateString(), // ✅ теперь строка
            endDate: now()->addDays(7)->toDateString()
        );

        $result = $repository->update($event, $dto);

        $this->assertTrue($result);
        $this->assertEquals('Updated Event', $event->fresh()->name);
    }

    public function testDeleteEventSuccessfully(): void
    {
        $this->actingAs(User::factory()->create());

        $event = Event::factory()->create([
                                              'user_id' => auth()->id(),
                                          ]);

        $repository = new EventRepository();

        $result = $repository->delete($event);

        $this->assertTrue($result);
        // Здесь важно: если в Event включено SoftDeletes — используем assertSoftDeleted
        $this->assertSoftDeleted('events', ['id' => $event->id]);
    }
}
