<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class ItemStoreDTO
{
    public function __construct(
        private string $name,
        private ?string $description,
        private int $quantity,
        private ?string $size,
        private ?string $material,
        private ?string $supplier,
        private ?string $storageLocation,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['name'],
            $data['description'] ?? null,
            (int) $data['quantity'],
            $data['size'] ?? null,
            $data['material'] ?? null,
            $data['supplier'] ?? null,
            $data['storage_location'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'size' => $this->size,
            'material' => $this->material,
            'supplier' => $this->supplier,
            'storage_location' => $this->storageLocation,
        ];
    }
}
