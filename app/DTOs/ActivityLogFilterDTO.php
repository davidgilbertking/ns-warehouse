<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class ActivityLogFilterDTO
{
    private ?string $user;
    private ?string $action;
    private ?string $entityType;
    private ?string $description;
    private ?string $date;

    public function __construct(
        ?string $user = null,
        ?string $action = null,
        ?string $entityType = null,
        ?string $description = null,
        ?string $date = null
    ) {
        $this->user = $user;
        $this->action = $action;
        $this->entityType = $entityType;
        $this->description = $description;
        $this->date = $date;
    }

    /**
     * @param array{
     *     user?: string,
     *     action?: string,
     *     entity_type?: string,
     *     description?: string,
     *     date?: string
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['user'] ?? null,
            $data['action'] ?? null,
            $data['entity_type'] ?? null,
            $data['description'] ?? null,
            $data['date'] ?? null,
        );
    }

    public function getUser(): ?string
    {
        return $this->user;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function getEntityType(): ?string
    {
        return $this->entityType;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getDate(): ?string
    {
        return $this->date;
    }
}
