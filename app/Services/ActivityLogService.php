<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\ActivityLogRepository;
use App\DTOs\ActivityLogFilterDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ActivityLogService
{
    protected ActivityLogRepository $repository;

    public function __construct(ActivityLogRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getLogs(ActivityLogFilterDTO $filters): LengthAwarePaginator
    {
        return $this->repository->search($filters);
    }
}
