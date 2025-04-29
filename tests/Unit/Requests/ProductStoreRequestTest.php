<?php

declare(strict_types=1);

namespace Tests\Unit\Requests;

use App\Http\Requests\ProductStoreRequest;
use App\Models\Item;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductStoreRequestTest extends TestCase
{
    use RefreshDatabase;

    public function testValidationPassesWithValidData(): void
    {
        $item = Item::factory()->create();

        $data = [
            'name' => 'New Product',
            'items' => [
                ['id' => $item->id, 'quantity' => 2],
            ],
        ];

        $validator = Validator::make($data, (new ProductStoreRequest())->rules());

        $this->assertTrue($validator->passes());
    }

    public function testValidationFailsWithInvalidData(): void
    {
        $data = [
            'name' => '',
            'items' => [
                ['id' => 'invalid', 'quantity' => 0],
            ],
        ];

        $validator = Validator::make($data, (new ProductStoreRequest())->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
        $this->assertArrayHasKey('items.0.id', $validator->errors()->toArray());
        $this->assertArrayHasKey('items.0.quantity', $validator->errors()->toArray());
    }
}
