<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class ProductStoreDTO
{
    public function __construct(
        public string $name,
        public ?array $items = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['name'],
            $data['items'] ?? null,
        );
    }
}
