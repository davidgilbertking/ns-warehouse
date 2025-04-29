<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function testUserFillableAttributes(): void
    {
        $user = User::factory()->make([
                                          'name' => 'Alice',
                                          'email' => 'alice@example.com',
                                          'password' => 'password',
                                          'role' => 'user',
                                      ]);

        $this->assertSame('Alice', $user->name);
        $this->assertSame('alice@example.com', $user->email);
        $this->assertSame('user', $user->role);
    }

    public function testUserHiddenAttributes(): void
    {
        $user = User::factory()->make([
                                          'password' => 'secret',
                                          'remember_token' => 'token123',
                                      ]);

        $array = $user->toArray();

        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('remember_token', $array);
    }

    public function testUserRoleHelpers(): void
    {
        $admin = User::factory()->make(['role' => 'admin']);
        $user = User::factory()->make(['role' => 'user']);
        $viewer = User::factory()->make(['role' => 'viewer']);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isUser());

        $this->assertTrue($user->isUser());
        $this->assertFalse($user->isAdmin());

        $this->assertTrue($viewer->isViewer());
        $this->assertFalse($viewer->isAdmin());
    }

    public function testUserHasManyEvents(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->events->contains($event));
    }
}
