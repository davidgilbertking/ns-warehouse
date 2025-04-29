<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers;

use App\Models\Event;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testIndexPageIsAccessible(): void
    {
        // Arrange
        $user = User::factory()->create();
        $this->actingAs($user);

        Event::factory()->count(3)->create();

        // Act
        $response = $this->get(route('events.index'));

        // Assert
        $response->assertStatus(200);
        $response->assertViewIs('events.index');
        $response->assertViewHas('events');
    }

    public function testCreatePageIsAccessible(): void
    {
        // Arrange
        $user = User::factory()->create();
        $this->actingAs($user);

        // Act
        $response = $this->get(route('events.create'));

        // Assert
        $response->assertStatus(200);
        $response->assertViewIs('events.create');
    }

    public function testStoreCreatesEventAndRedirects(): void
    {
        // Arrange
        $user = User::factory()->create();
        $this->actingAs($user);

        Item::factory()->create(['id' => 1]);
        Item::factory()->create(['id' => 2]);

        $data = [
            'name' => 'Test Event',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
            'items' => [
                ['id' => 1, 'quantity' => 2],
                ['id' => 2, 'quantity' => 3],
            ],
        ];

        // Act
        $response = $this->post(route('events.store'), $data);

        // Assert
        $response->assertRedirect(route('events.index'));

        $this->assertDatabaseHas('events', [
            'name' => 'Test Event',
            'user_id' => $user->id,
        ]);
    }
    public function testUpdateUpdatesEventAndRedirects(): void
    {
        // Arrange
        $user = User::factory()->create();
        $this->actingAs($user);

        $event = Event::factory()->create([
                                              'user_id' => $user->id,
                                              'name' => 'Old Name',
                                              'start_date' => now()->addDays(1)->toDateString(),
                                              'end_date' => now()->addDays(2)->toDateString(),
                                          ]);

        $items = Item::factory()->count(2)->create();

        $payload = [
            'name' => 'Updated Name',
            'start_date' => now()->addDays(3)->toDateString(),
            'end_date' => now()->addDays(4)->toDateString(),
            'items' => [
                ['id' => $items[0]->id, 'quantity' => 3],
                ['id' => $items[1]->id, 'quantity' => 2],
            ],
        ];

        // Act
        $response = $this->patch(route('events.update', $event), $payload);

        // Assert
        $response->assertRedirect(route('events.show', $event));
        $response->assertSessionHas('success', 'Мероприятие обновлено!');

        $this->assertDatabaseCount('events', 1);

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'name' => 'Updated Name',
        ]);

        $this->assertDatabaseHas('events', [
            'start_date' => $event->fresh()->start_date->toDateTimeString(),
            'end_date'   => $event->fresh()->end_date->toDateTimeString(),
        ]);
    }
    public function testDestroyDeletesEventAndRedirects(): void
    {
        // Arrange
        $user = User::factory()->create();
        $this->actingAs($user);

        $event = Event::factory()->create([
                                              'user_id' => $user->id,
                                          ]);

        // Act
        $response = $this->delete(route('events.destroy', $event));

        // Assert
        $response->assertRedirect(route('events.index'));
        $response->assertSessionHas('success', 'Мероприятие удалено!');

        $this->assertSoftDeleted('events', [
            'id' => $event->id,
        ]);
    }

}
