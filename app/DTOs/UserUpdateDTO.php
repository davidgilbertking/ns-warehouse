<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class UserUpdateDTO
{
    public function __construct(
        private string $email,
        private string $role,
        private ?string $password = null,
    ) {}

    /**
     * @param array{
     *     email: string,
     *     role: string,
     *     password?: string|null
     * } $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['email'],
            $data['role'],
            $data['password'] ?? null,
        );
    }


    public function getEmail(): string
    {
        return $this->email;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }
}
