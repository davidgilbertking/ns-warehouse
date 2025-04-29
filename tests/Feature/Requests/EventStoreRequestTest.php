<?php

namespace Tests\Feature\Requests;

use Tests\TestCase;
use App\Http\Requests\EventStoreRequest;
use Illuminate\Support\Facades\Validator;

class EventStoreRequestTest extends TestCase
{
    public function testValidationPassesWithValidData(): void
    {
        $data = [
            'name' => 'Test Event',
            'start_date' => '2025-05-01',
            'end_date' => '2025-05-02',
        ];

        $request = new EventStoreRequest();
        $rules = $request->rules();

        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    public function testValidationFailsWithMissingName(): void
    {
        $data = [
            'start_date' => '2025-05-01',
            'end_date' => '2025-05-02',
        ];

        $request = new EventStoreRequest();
        $rules = $request->rules();

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function testValidationFailsWithInvalidDates(): void
    {
        $data = [
            'name' => 'Test Event',
            'start_date' => 'invalid-date',
            'end_date' => 'another-invalid-date',
        ];

        $request = new EventStoreRequest();
        $rules = $request->rules();

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('start_date', $validator->errors()->toArray());
        $this->assertArrayHasKey('end_date', $validator->errors()->toArray());
    }
}
