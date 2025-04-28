<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class EventStoreDTO
{
    public function __construct(
        public string $name,
        public string $startDate,
        public string $endDate,
        public ?array $items = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['name'],
            $data['start_date'],
            $data['end_date'],
            $data['items'] ?? [],
        );
    }
}
