<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\ActivityLog;
use App\DTOs\ActivityLogFilterDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ActivityLogRepository
{
    public function search(ActivityLogFilterDTO $filters): LengthAwarePaginator
    {
        $query = ActivityLog::with('user')->orderBy('created_at', 'desc');

        if ($filters->getUser() !== null) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('name', 'ILIKE', '%' . $filters->getUser() . '%');
            });
        }

        if ($filters->getAction() !== null) {
            $query->where('action', 'ILIKE', '%' . $filters->getAction() . '%');
        }

        if ($filters->getEntityType() !== null) {
            $query->where('entity_type', 'ILIKE', '%' . $filters->getEntityType() . '%');
        }

        if ($filters->getDescription() !== null) {
            $query->where('description', 'ILIKE', '%' . $filters->getDescription() . '%');
        }

        if ($filters->getDate() !== null) {
            $query->whereDate('created_at', $filters->getDate());
        }

        return $query->paginate(15);
    }
}
