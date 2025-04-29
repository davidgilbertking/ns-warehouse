<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Models\Event;
use App\Models\Item;
use App\Models\Reservation;
use App\Services\EventExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class EventExportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Создаём только таблицу reservations
        if (!Schema::hasTable('reservations')) {
            Schema::create('reservations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('event_id')->constrained()->cascadeOnDelete();
                $table->foreignId('item_id')->constrained()->cascadeOnDelete();
                $table->integer('quantity');
                $table->timestamps();
            });
        }
    }

    public function testExportGeneratesCsvCorrectly(): void
    {
        // Arrange
        $event = Event::factory()->create([
                                              'name' => 'Test Event',
                                              'start_date' => now(),
                                              'end_date' => now()->addDays(2),
                                          ]);

        $item = Item::create([
                                 'name' => 'Test Item',
                             ]);

        Reservation::create([
                                'event_id' => $event->id,
                                'item_id' => $item->id,
                                'quantity' => 5,
                            ]);

        $service = new EventExportService();

        // Act
        $csv = $service->exportItems($event);

        // Assert
        $this->assertStringContainsString('Название мероприятия', $csv);
        $this->assertStringContainsString('Test Event', $csv);
        $this->assertStringContainsString('Название предмета', $csv);
        $this->assertStringContainsString('Test Item', $csv);
        $this->assertStringContainsString('5', $csv);
    }
}
