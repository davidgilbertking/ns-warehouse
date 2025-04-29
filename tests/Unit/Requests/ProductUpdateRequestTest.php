<?php

declare(strict_types=1);

namespace Tests\Unit\Requests;

use App\Http\Requests\ProductUpdateRequest;
use App\Models\Item;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductUpdateRequestTest extends TestCase
{
    use RefreshDatabase;

    public function testValidationPassesWithValidData(): void
    {
        $item = Item::factory()->create();

        $data = [
            'name' => 'Updated Product',
            'items' => [
                ['id' => $item->id, 'quantity' => 5],
            ],
        ];

        $validator = Validator::make($data, (new ProductUpdateRequest())->rules());

        $this->assertTrue($validator->passes());
    }

    public function testValidationFailsWithInvalidData(): void
    {
        $data = [
            'name' => null,
            'items' => [
                ['id' => null, 'quantity' => -5],
            ],
        ];

        $validator = Validator::make($data, (new ProductUpdateRequest())->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
        $this->assertArrayHasKey('items.0.id', $validator->errors()->toArray());
        $this->assertArrayHasKey('items.0.quantity', $validator->errors()->toArray());
    }
}
