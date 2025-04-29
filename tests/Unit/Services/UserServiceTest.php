<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\DTOs\UserStoreDTO;
use App\DTOs\UserUpdateDTO;
use App\Models\ActivityLog;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    public function testCreateUserSuccessfully(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $dto = new UserStoreDTO(
            name: 'John Doe',
            email: 'john@example.com',
            password: 'secret123',
            role: 'user'
        );

        $service = app(UserService::class);
        $user = $service->createUser($dto);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'role' => 'user',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'created_user',
            'entity_type' => 'User',
            'entity_id' => $user->id,
        ]);
    }

    public function testUpdateUserSuccessfully(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $user = User::factory()->create(['role' => 'user']);

        $dto = new UserUpdateDTO(
            email: 'updated@example.com',
            role: 'viewer',
            password: 'newpassword123'
        );

        $service = app(UserService::class);
        $result = $service->updateUser($dto, $user);

        $this->assertTrue($result);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'updated@example.com',
            'role' => 'viewer',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'updated_user',
            'entity_type' => 'User',
            'entity_id' => $user->id,
        ]);
    }

    public function testDeleteUserSuccessfully(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $user = User::factory()->create();

        $service = app(UserService::class);
        $result = $service->deleteUser($user);

        $this->assertTrue($result);

        $this->assertSoftDeleted('users', [
            'id' => $user->id,
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'deleted_user',
            'entity_type' => 'User',
            'entity_id' => $user->id,
        ]);
    }
}
