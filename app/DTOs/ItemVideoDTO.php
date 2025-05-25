<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class ItemVideoDTO
{
    public function __construct(
        private string $path,
    ) {}

    public function getPath(): string
    {
        return $this->path;
    }
}
