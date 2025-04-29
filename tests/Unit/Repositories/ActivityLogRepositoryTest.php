<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Models\ActivityLog;
use App\Models\User;
use App\Repositories\ActivityLogRepository;
use App\DTOs\ActivityLogFilterDTO;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ActivityLogRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ActivityLogRepository();
    }

    public function testSearchReturnsFilteredLogs(): void
    {
        // Arrange
        $user = User::factory()->create(['name' => 'Alice']);

        $log = ActivityLog::create([
                                       'user_id'     => $user->id,
                                       'action'      => 'created_item',
                                       'entity_type' => 'Item',
                                       'entity_id'   => 1,
                                       'description' => 'Item created successfully',
                                       'created_at'  => now(),
                                   ]);

        // Оставляем фильтры пустыми
        $dto = new ActivityLogFilterDTO();

        $repository = new ActivityLogRepository();

        // Act
        $results = $repository->search($dto);

        // Assert
        $this->assertGreaterThan(0, $results->total());
        $this->assertTrue($results->contains('id', $log->id));
    }
}
