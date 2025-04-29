<?php

declare(strict_types=1);

namespace Tests\Unit\Requests;

use App\Http\Requests\ActivityLogRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ActivityLogRequestTest extends TestCase
{
    public function testValidationPassesWithValidData(): void
    {
        $data = [
            'user' => 'John Doe',
            'action' => 'created_item',
            'entity_type' => 'Item',
            'description' => 'Some description',
            'date' => now()->toDateString(),
        ];

        $request = new ActivityLogRequest();
        $rules = $request->rules();

        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    public function testValidationFailsWithInvalidData(): void
    {
        $data = [
            'user' => str_repeat('a', 300), // слишком длинное имя
            'action' => str_repeat('b', 300),
            'entity_type' => str_repeat('c', 300),
            'description' => str_repeat('d', 600), // слишком длинное описание
            'date' => 'invalid-date',
        ];

        $request = new ActivityLogRequest();
        $rules = $request->rules();

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
    }
}
