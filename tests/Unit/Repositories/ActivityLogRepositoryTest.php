<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\DTOs\ActivityLogFilterDTO;
use App\Models\ActivityLog;
use App\Models\User;
use App\Repositories\ActivityLogRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ActivityLogRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ActivityLogRepository;
    }

    public function test_search_returns_filtered_logs(): void
    {
        // Arrange
        $user = User::factory()->create(['name' => 'Alice']);

        $log = ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'created_item',
            'entity_type' => 'Item',
            'entity_id' => 1,
            'description' => 'Item created successfully',
            'created_at' => now(),
        ]);

        // Оставляем фильтры пустыми
        $dto = new ActivityLogFilterDTO;

        $repository = new ActivityLogRepository;

        // Act
        $results = $repository->search($dto);

        // Assert
        $this->assertGreaterThan(0, $results->total());
        $this->assertTrue($results->contains('id', $log->id));
    }

    public function test_search_filters_are_case_insensitive_for_latin_and_cyrillic(): void
    {
        $user = User::factory()->create(['name' => 'АЛИСА ABC']);
        $matchingLog = ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'ABC_ДЕЙСТВИЕ',
            'entity_type' => 'АБВ_ENTITY',
            'entity_id' => 1,
            'description' => 'Описание АБВ ABC',
        ]);

        $otherUser = User::factory()->create(['name' => 'Other User']);
        ActivityLog::create([
            'user_id' => $otherUser->id,
            'action' => 'other_action',
            'entity_type' => 'Other',
            'entity_id' => 2,
            'description' => 'Other description',
        ]);

        $filters = [
            new ActivityLogFilterDTO(user: 'алиса abc'),
            new ActivityLogFilterDTO(action: 'abc_действие'),
            new ActivityLogFilterDTO(entityType: 'абв_entity'),
            new ActivityLogFilterDTO(description: 'описание абв'),
        ];

        foreach ($filters as $filter) {
            $results = $this->repository->search($filter);

            $this->assertTrue($results->contains('id', $matchingLog->id));
            $this->assertSame(1, $results->total());
        }
    }
}
