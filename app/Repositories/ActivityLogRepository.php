<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTOs\ActivityLogFilterDTO;
use App\Models\ActivityLog;
use App\Support\UnicodeSearch;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ActivityLogRepository
{
    public function search(ActivityLogFilterDTO $filters): LengthAwarePaginator
    {
        $query = ActivityLog::with('user')->orderBy('created_at', 'desc');

        if ($filters->getDate() !== null) {
            $query->whereDate('created_at', $filters->getDate());
        }

        $logs = $query->get();

        if (UnicodeSearch::term($filters->getUser()) !== null) {
            $logs = $logs->filter(
                fn (ActivityLog $log): bool => UnicodeSearch::contains($log->user?->name, $filters->getUser())
            );
        }

        if (UnicodeSearch::term($filters->getAction()) !== null) {
            $logs = $logs->filter(
                fn (ActivityLog $log): bool => UnicodeSearch::contains($log->action, $filters->getAction())
            );
        }

        if (UnicodeSearch::term($filters->getEntityType()) !== null) {
            $logs = $logs->filter(
                fn (ActivityLog $log): bool => UnicodeSearch::contains($log->entity_type, $filters->getEntityType())
            );
        }

        if (UnicodeSearch::term($filters->getDescription()) !== null) {
            $logs = $logs->filter(
                fn (ActivityLog $log): bool => UnicodeSearch::contains($log->description, $filters->getDescription())
            );
        }

        return UnicodeSearch::paginate($logs, 15);
    }
}
