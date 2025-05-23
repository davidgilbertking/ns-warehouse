<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class ItemUpdateDTO
{
    public function __construct(
        private string $name,
        private ?string $description,
        private int $quantity,
        private ?string $size,
        private ?string $material,
        private ?string $supplier,
        private ?string $storageLocation,
        private ?string $mechanics,
        private ?string $scalability,
        private ?float $clientPrice,
        private ?string $brandingOptions,
        private ?string $adaptationOptions,
        private ?string $opPrice,
        private ?string $constructionDescription,
        private ?string $contractor,
        private ?string $productionCost,
        private ?string $changeHistory,
        private ?string $consumables,
        private ?string $implementationComments,
        private ?string $mounting,
        private ?string $storageFeatures,
        private ?string $designLinks,
        private ?string $eventHistory,
        private ?string $storagePlace,
        private ?array $opMedia = [],
        private ?array $realMedia = [],
        private ?array $eventMedia = [],
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
            $data['mechanics'] ?? null,
            $data['scalability'] ?? null,
            isset($data['client_price']) ? (float) $data['client_price'] : null,
            $data['branding_options'] ?? null,
            $data['adaptation_options'] ?? null,
            $data['op_price'] ?? null,
            $data['construction_description'] ?? null,
            $data['contractor'] ?? null,
            $data['production_cost'] ?? null,
            $data['change_history'] ?? null,
            $data['consumables'] ?? null,
            $data['implementation_comments'] ?? null,
            $data['mounting'] ?? null,
            $data['storage_features'] ?? null,
            $data['design_links'] ?? null,
            $data['event_history'] ?? null,
            $data['storage_place'] ?? null,
            $data['op_media'] ?? [],
            $data['real_media'] ?? [],
            $data['event_media'] ?? [],
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
            'mechanics' => $this->mechanics,
            'scalability' => $this->scalability,
            'client_price' => $this->clientPrice,
            'branding_options' => $this->brandingOptions,
            'adaptation_options' => $this->adaptationOptions,
            'op_price' => $this->opPrice,
            'construction_description' => $this->constructionDescription,
            'contractor' => $this->contractor,
            'production_cost' => $this->productionCost,
            'change_history' => $this->changeHistory,
            'consumables' => $this->consumables,
            'implementation_comments' => $this->implementationComments,
            'mounting' => $this->mounting,
            'storage_features' => $this->storageFeatures,
            'design_links' => $this->designLinks,
            'event_history' => $this->eventHistory,
            'storage_place' => $this->storagePlace,
            'op_media' => $this->opMedia,
            'real_media' => $this->realMedia,
            'event_media' => $this->eventMedia,
        ];
    }
}
