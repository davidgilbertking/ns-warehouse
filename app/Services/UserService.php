<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ActivityLog;
use App\Repositories\UserRepository;
use App\DTOs\UserStoreDTO;
use App\DTOs\UserUpdateDTO;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class UserService
{
    public function __construct(
        protected UserRepository $repository
    ) {
    }

    public function getPaginatedUsers(int $perPage = 10): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    public function createUser(UserStoreDTO $dto): User
    {
        $data = [
            'name'     => $dto->getName(),
            'email'    => $dto->getEmail(),
            'password' => bcrypt($dto->getPassword()),
            'role'     => $dto->getRole(),
        ];

        $user = $this->repository->create($data);

        $this->logAction('created_user', $user);

        return $user;
    }

    public function updateUser(UserUpdateDTO $dto, User $user): bool
    {
        $data = [
            'email' => $dto->getEmail(),
            'role'  => $dto->getRole(),
        ];

        if ($dto->getPassword() !== null) {
            $data['password'] = bcrypt($dto->getPassword());
        }

        $updated = $this->repository->update($user, $data);

        if ($updated) {
            $this->logAction('updated_user', $user);
        }

        return $updated;
    }

    public function deleteUser(User $user): bool
    {
        $deleted = $this->repository->delete($user);

        if ($deleted) {
            $this->logAction('deleted_user', $user);
        }

        return $deleted;
    }

    protected function logAction(string $action, User $user): void
    {
        if (Auth::check()) {
            ActivityLog::create([
                                    'user_id'     => Auth::id(),
                                    'action'      => $action,
                                    'entity_type' => 'User',
                                    'entity_id'   => $user->id,
                                    'description' => ucfirst(str_replace('_', ' ', $action)) . ": {$user->email}",
                                ]);
        }
    }
}
