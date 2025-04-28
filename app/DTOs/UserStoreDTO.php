<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class UserStoreDTO
{
    public function __construct(
        private string $name,
        private string $email,
        private string $password,
        private string $role,
    ) {}

    /**
     * @param array{
     *     name: string,
     *     email: string,
     *     password: string,
     *     role: string
     * } $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['name'],
            $data['email'],
            $data['password'],
            $data['role'],
        );
    }


    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getRole(): string
    {
        return $this->role;
    }
}
