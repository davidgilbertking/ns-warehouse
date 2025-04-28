<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class ItemImageDTO
{
    public function __construct(
        private string $path,
    ) {}

    public function getPath(): string
    {
        return $this->path;
    }
}
