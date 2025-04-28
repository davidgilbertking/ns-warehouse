<?php

declare(strict_types=1);

namespace App\DTOs;

class ActivityLogFilterDTO
{
    private readonly ?string $user;
    private readonly ?string $action;
    private readonly ?string $entityType;
    private readonly ?string $description;
    private readonly ?string $date;

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
