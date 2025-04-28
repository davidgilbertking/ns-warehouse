<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class EventCreateInputDTO
{
    public function __construct(
        public ?int $productId = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            isset($data['product_id']) ? (int)$data['product_id'] : null,
        );
    }
}
