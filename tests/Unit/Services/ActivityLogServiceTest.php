<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\ActivityLogService;
use App\Repositories\ActivityLogRepository;
use App\DTOs\ActivityLogFilterDTO;
use Illuminate\Pagination\LengthAwarePaginator;
use PHPUnit\Framework\TestCase;

class ActivityLogServiceTest extends TestCase
{
    public function testGetLogsReturnsPaginator(): void
    {
        // Arrange
        $dto = new ActivityLogFilterDTO();
        $mockPaginator = $this->createMock(LengthAwarePaginator::class);

        $repo = $this->createMock(ActivityLogRepository::class);
        $repo->expects($this->once())
             ->method('search')
             ->with($dto)
             ->willReturn($mockPaginator);

        $service = new ActivityLogService($repo);

        // Act
        $result = $service->getLogs($dto);

        // Assert
        $this->assertSame($mockPaginator, $result);
    }
}
