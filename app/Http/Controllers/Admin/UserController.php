<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\DTOs\UserStoreDTO;
use App\DTOs\UserUpdateDTO;
use App\Models\User;

class UserController extends Controller
{
    public function __construct(
        protected UserService $service
    ) {}

    public function index()
    {
        $users = $this->service->getPaginatedUsers();
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(UserStoreRequest $request)
    {
        $dto = UserStoreDTO::fromArray($request->validated());
        $this->service->createUser($dto);

        return redirect()->route('admin.users.index')->with('success', 'Пользователь создан!');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(UserUpdateRequest $request, User $user)
    {
        $dto = UserUpdateDTO::fromArray($request->validated());
        $this->service->updateUser($dto, $user);

        return redirect()->route('admin.users.index')->with('success', 'Пользователь обновлён.');
    }

    public function destroy(User $user)
    {
        if ($user->isAdmin()) {
            return redirect()->route('admin.users.index')->with('error', 'Нельзя удалить другого администратора!');
        }

        $this->service->deleteUser($user);

        return redirect()->route('admin.users.index')->with('success', 'Пользователь удалён!');
    }
}
