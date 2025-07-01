<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class ItemFilterDTO
{
    public function __construct(
        private ?string $search = null,
        private ?string $availableFrom = null,
        private ?string $availableTo = null,
        private ?string $product = null,
        private ?int $depth = null, // ⬅️ добавляем поле для depth
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['search'] ?? null,
            $data['available_from'] ?? null,
            $data['available_to'] ?? null,
            $data['product'] ?? null,
            isset($data['depth']) ? (int)$data['depth'] : null, // ⬅️ парсим depth из запроса
        );
    }

    public function getSearch(): ?string
    {
        return $this->search;
    }

    public function getAvailableFrom(): ?string
    {
        return $this->availableFrom;
    }

    public function getAvailableTo(): ?string
    {
        return $this->availableTo;
    }

    public function getProduct(): ?string
    {
        return $this->product;
    }

    public function getDepth(): ?int
    {
        return $this->depth;
    }
}
