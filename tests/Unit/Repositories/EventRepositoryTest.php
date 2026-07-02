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

    public function test_create_event_successfully(): void
    {
        $this->actingAs(User::factory()->create());

        $repository = new EventRepository;

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

    public function test_update_event_successfully(): void
    {
        $this->actingAs(User::factory()->create());

        $event = Event::factory()->create([
            'name' => 'Old Name',
            'start_date' => now()->addDays(1)->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'user_id' => auth()->id(),
        ]);

        $repository = new EventRepository;

        $dto = new EventUpdateDTO(
            name: 'Updated Event',
            startDate: now()->addDays(5)->toDateString(), // ✅ теперь строка
            endDate: now()->addDays(7)->toDateString()
        );

        $result = $repository->update($event, $dto);

        $this->assertTrue($result);
        $this->assertEquals('Updated Event', $event->fresh()->name);
    }

    public function test_delete_event_successfully(): void
    {
        $this->actingAs(User::factory()->create());

        $event = Event::factory()->create([
            'user_id' => auth()->id(),
        ]);

        $repository = new EventRepository;

        $result = $repository->delete($event);

        $this->assertTrue($result);
        // Здесь важно: если в Event включено SoftDeletes — используем assertSoftDeleted
        $this->assertSoftDeleted('events', ['id' => $event->id]);
    }

    public function test_get_filtered_events_search_is_case_insensitive_for_latin_and_cyrillic(): void
    {
        $repository = new EventRepository;
        $latinEvent = Event::factory()->create([
            'name' => 'ABC Event',
            'start_date' => now()->addDays(2)->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
        ]);
        $cyrillicEvent = Event::factory()->create([
            'name' => 'АБВ Мероприятие',
            'start_date' => now()->addDays(4)->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
        ]);
        Event::factory()->create([
            'name' => 'Other Event',
            'start_date' => now()->addDays(6)->toDateString(),
            'end_date' => now()->addDays(7)->toDateString(),
        ]);

        $latinResults = $repository->getFilteredEvents(['search' => 'abc']);
        $cyrillicResults = $repository->getFilteredEvents(['search' => 'абв']);

        $this->assertTrue(collect($latinResults->items())->contains('id', $latinEvent->id));
        $this->assertTrue(collect($cyrillicResults->items())->contains('id', $cyrillicEvent->id));
        $this->assertSame(1, $latinResults->total());
        $this->assertSame(1, $cyrillicResults->total());
    }
}
