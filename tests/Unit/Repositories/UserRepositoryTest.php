<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected UserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new UserRepository();
    }

    public function testCreateUser(): void
    {
        $data = [
            'name' => 'Bob',
            'email' => 'bob@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'user',
        ];

        $user = $this->repository->create($data);

        $this->assertDatabaseHas('users', ['email' => 'bob@example.com']);
        $this->assertSame('Bob', $user->name);
    }

    public function testUpdateUser(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $updated = $this->repository->update($user, ['role' => 'viewer']);

        $this->assertTrue($updated);
        $this->assertSame('viewer', $user->fresh()->role);
    }

    public function testDeleteUser(): void
    {
        $user = User::factory()->create();
        $deleted = $this->repository->delete($user);

        $this->assertTrue($deleted);
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    public function testPaginateUsers(): void
    {
        User::factory()->count(15)->create();
        $result = $this->repository->paginate(10);

        $this->assertCount(10, $result);
    }
}
