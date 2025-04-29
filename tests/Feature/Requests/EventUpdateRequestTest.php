<?php

namespace Tests\Feature\Requests;

use Tests\TestCase;
use App\Http\Requests\EventUpdateRequest;
use Illuminate\Support\Facades\Validator;

class EventUpdateRequestTest extends TestCase
{
    public function testValidationPassesWithValidData(): void
    {
        $data = [
            'name' => 'Updated Event Name',
            'start_date' => '2025-05-01',
            'end_date' => '2025-05-03',
        ];

        $request = new EventUpdateRequest();
        $rules = $request->rules();

        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    public function testValidationFailsWithMissingRequiredFields(): void
    {
        $data = [];

        $request = new EventUpdateRequest();
        $rules = $request->rules();

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('name', $validator->errors()->messages());
        $this->assertArrayHasKey('start_date', $validator->errors()->messages());
        $this->assertArrayHasKey('end_date', $validator->errors()->messages());
    }

}
