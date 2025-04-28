<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\DTOs\ItemFilterDTO;
use App\Models\Item;
use App\Models\Event;
use App\Models\Reservation;
use App\Repositories\ItemRepository;
use App\Services\ItemExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemExportServiceTest extends TestCase
{
    use RefreshDatabase;

    public function testExportGeneratesCsvWithoutDateFilters(): void
    {
        // Arrange
        $repository = new ItemRepository();
        $service = new ItemExportService($repository);

        $item = Item::factory()->create([
                                            'name' => 'Test Item',
                                            'description' => 'Test Description',
                                            'quantity' => 10,
                                        ]);

        $filter = new ItemFilterDTO();

        // Act
        $csvContent = $service->export($filter);

        // Assert
        $this->assertStringContainsString('Название', $csvContent);
        $this->assertStringContainsString('Описание', $csvContent);
        $this->assertStringContainsString('Количество всего', $csvContent);
        $this->assertStringContainsString('Количество доступно', $csvContent);
        $this->assertStringContainsString('Test Item', $csvContent);
        $this->assertStringContainsString('Test Description', $csvContent);
        $this->assertStringContainsString('10', $csvContent);

    }

    public function testExportGeneratesCsvWithAvailableQuantity(): void
    {
        // Arrange
        $repository = new ItemRepository();
        $service = new ItemExportService($repository);

        $item = Item::factory()->create([
                                            'name' => 'Reserved Item',
                                            'description' => 'Reserved Description',
                                            'quantity' => 10,
                                        ]);

        $event = Event::factory()->create([
                                              'start_date' => now()->addDays(1),
                                              'end_date' => now()->addDays(2),
                                          ]);

        Reservation::create([
                                'item_id' => $item->id,
                                'event_id' => $event->id,
                                'quantity' => 3,
                            ]);

        $filter = new ItemFilterDTO(
            availableFrom: now()->toDateString(),
            availableTo: now()->addDays(5)->toDateString(),
        );

        // Act
        $csvContent = $service->export($filter);

        // Assert
        $this->assertStringContainsString('Reserved Item', $csvContent);
        $this->assertStringContainsString('7', $csvContent); // 10 - 3 = 7 available
    }
}
