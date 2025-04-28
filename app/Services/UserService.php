<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\UserRepository;
use App\DTOs\UserStoreDTO;
use App\DTOs\UserUpdateDTO;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserService
{
    public function __construct(
        protected UserRepository $repository
    ) {}

    public function getPaginatedUsers(int $perPage = 10): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    public function createUser(UserStoreDTO $dto): User
    {
        $data = [
            'name' => $dto->getName(),
            'email' => $dto->getEmail(),
            'password' => bcrypt($dto->getPassword()),
            'role' => $dto->getRole(),
        ];

        return $this->repository->create($data);
    }

    public function updateUser(UserUpdateDTO $dto, User $user): bool
    {
        $data = [
            'email' => $dto->getEmail(),
            'role' => $dto->getRole(),
        ];

        if ($dto->getPassword() !== null) {
            $data['password'] = bcrypt($dto->getPassword());
        }

        return $this->repository->update($user, $data);
    }

    public function deleteUser(User $user): bool
    {
        return $this->repository->delete($user);
    }
}
