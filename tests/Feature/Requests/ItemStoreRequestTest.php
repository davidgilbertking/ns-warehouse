<?php

declare(strict_types=1);

namespace Tests\Feature\Requests;

use App\Http\Requests\ItemStoreRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ItemStoreRequestTest extends TestCase
{
    public function testValidDataPassesValidation(): void
    {
        $data = [
            'name' => 'Test Item',
            'description' => 'Test Description',
            'quantity' => 10,
            'size' => 'Medium',
            'material' => 'Plastic',
            'supplier' => 'Test Supplier',
            'storage_location' => 'Shelf A1',
        ];

        $validator = Validator::make($data, (new ItemStoreRequest())->rules());

        $this->assertTrue($validator->passes());
    }

    public function testMissingNameFailsValidation(): void
    {
        $data = [
            'description' => 'Test Description',
            'quantity' => 10,
        ];

        $validator = Validator::make($data, (new ItemStoreRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function testNegativeQuantityFailsValidation(): void
    {
        $data = [
            'name' => 'Test Item',
            'description' => 'Test Description',
            'quantity' => -5,
        ];

        $validator = Validator::make($data, (new ItemStoreRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('quantity', $validator->errors()->toArray());
    }
}
