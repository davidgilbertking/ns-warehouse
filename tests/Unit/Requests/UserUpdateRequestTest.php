<?php

declare(strict_types=1);

namespace Tests\Unit\Requests;

use App\Http\Requests\UserUpdateRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route;
use Illuminate\Routing\RouteCollection;
use Illuminate\Routing\Router;
use Illuminate\Http\Request;
use Tests\TestCase;
use App\Models\User;

class UserUpdateRequestTest extends TestCase
{
    use RefreshDatabase;

    public function testValidationPassesWithValidData(): void
    {
        $user = User::factory()->create();

        $data = [
            'email' => 'new@example.com',
            'role' => 'user',
            'password' => 'newpassword123',
        ];

        $request = UserUpdateRequest::create(
            "/admin/users/{$user->id}",
            'PUT',
            $data
        );

        $request->setContainer(app());

        $route = (new Route(['PUT'], '/admin/users/{user}', []))
            ->setContainer(app())
            ->bind($request);

        $route->setParameter('user', $user);
        $request->setRouteResolver(fn () => $route);

        $request->validateResolved(); // <-- запускает валидацию

        $this->assertTrue(true); // если не выбросило ValidationException — тест успешен
    }

    public function testValidationFailsWithInvalidData(): void
    {
        $user = User::factory()->create();
        User::factory()->create(['email' => 'taken@example.com']);

        $data = [
            'email' => 'taken@example.com',
            'role' => 'invalid-role',
            'password' => '123',
        ];

        $request = UserUpdateRequest::create(
            "/admin/users/{$user->id}",
            'PUT',
            $data
        );

        $request->setContainer(app());
        $request->setRedirector(app(\Illuminate\Routing\Redirector::class)); // << вот это обязательно

        $route = (new Route(['PUT'], '/admin/users/{user}', []))
            ->setContainer(app())
            ->bind($request);

        $route->setParameter('user', $user);
        $request->setRouteResolver(fn () => $route);

        try {
            $request->validateResolved();
            $this->fail('Validation should have failed but passed.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors()->toArray();

            $this->assertArrayHasKey('email', $errors);
            $this->assertArrayHasKey('role', $errors);
            $this->assertArrayHasKey('password', $errors);
        }
    }

}
