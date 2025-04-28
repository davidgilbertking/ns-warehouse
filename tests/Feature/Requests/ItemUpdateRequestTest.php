<?php

declare(strict_types=1);

namespace Tests\Feature\Requests;

use App\Http\Requests\ItemUpdateRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ItemUpdateRequestTest extends TestCase
{
    public function testValidDataPassesValidation(): void
    {
        $data = [
            'name' => 'Updated Item',
            'description' => 'Updated Description',
            'quantity' => 20,
            'size' => 'Large',
            'material' => 'Steel',
            'supplier' => 'Supplier Updated',
            'storage_location' => 'Shelf B2',
        ];

        $validator = Validator::make($data, (new ItemUpdateRequest())->rules());

        $this->assertTrue($validator->passes());
    }

    public function testMissingNameFailsValidation(): void
    {
        $data = [
            'description' => 'Updated Description',
            'quantity' => 20,
        ];

        $validator = Validator::make($data, (new ItemUpdateRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function testNegativeQuantityFailsValidation(): void
    {
        $data = [
            'name' => 'Updated Item',
            'description' => 'Updated Description',
            'quantity' => -10,
        ];

        $validator = Validator::make($data, (new ItemUpdateRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('quantity', $validator->errors()->toArray());
    }
}
