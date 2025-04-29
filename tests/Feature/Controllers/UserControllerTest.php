<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testIndexDisplaysUserList(): void
    {
        $users = User::factory()->count(3)->create();

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.index');
        $response->assertViewHas('users');
    }

    public function testCreatePageIsAccessible(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->get(route('admin.users.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.create');
    }

    public function testStoreCreatesUserAndRedirects(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $data = [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user',
        ];

        $response = $this->post(route('admin.users.store'), $data);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'new@example.com',
            'role' => 'user',
        ]);
    }

    public function testEditPageIsAccessible(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $user = User::factory()->create();

        $response = $this->get(route('admin.users.edit', $user));

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.edit');
        $response->assertViewHas('user', $user);
    }

    public function testUpdateUpdatesUserAndRedirects(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $user = User::factory()->create(['role' => 'user']);

        $response = $this->patch(route('admin.users.update', $user), [
            'email' => 'updated@example.com',
            'role' => 'viewer',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'updated@example.com',
            'role' => 'viewer',
        ]);
    }

    public function testDestroyDeletesUserAndRedirects(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $user = User::factory()->create(['role' => 'user']);

        $response = $this->delete(route('admin.users.destroy', $user));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    public function testDestroyDoesNotDeleteAdmin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $anotherAdmin = User::factory()->create(['role' => 'admin']);

        $response = $this->delete(route('admin.users.destroy', $anotherAdmin));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $anotherAdmin->id]);
    }
}
