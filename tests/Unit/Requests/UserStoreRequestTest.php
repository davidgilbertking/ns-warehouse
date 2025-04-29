<?php

declare(strict_types=1);

namespace Tests\Unit\Requests;

use App\Http\Requests\UserStoreRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class UserStoreRequestTest extends TestCase
{
    use RefreshDatabase;

    public function testValidationPassesWithValidData(): void
    {
        $data = [
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'role' => 'admin',
        ];

        $request = new UserStoreRequest();
        $rules = $request->rules();

        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    public function testValidationFailsWithInvalidData(): void
    {
        // Подготовка: создать пользователя, чтобы проверить unique
        User::factory()->create(['email' => 'existing@example.com']);

        $data = [
            'name' => '',
            'email' => 'existing@example.com', // уже есть
            'password' => 'short',
            'password_confirmation' => 'mismatch',
            'role' => 'invalid',
        ];

        $request = new UserStoreRequest();
        $rules = $request->rules();

        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->messages());
        $this->assertArrayHasKey('email', $validator->errors()->messages());
        $this->assertArrayHasKey('password', $validator->errors()->messages());
        $this->assertArrayHasKey('role', $validator->errors()->messages());
    }
}
