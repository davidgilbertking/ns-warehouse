<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Product;
use App\Models\Item;
use App\Services\ProductExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

class ProductExportServiceTest extends TestCase
{
    use RefreshDatabase;

    public function testExportReturnsStreamedResponseWithCorrectHeaders(): void
    {
        // Arrange
        $service = new ProductExportService();

        $product = Product::factory()->create();
        $item1 = Item::factory()->create();
        $item2 = Item::factory()->create();

        $product->items()->attach($item1->id, ['quantity' => 5]);
        $product->items()->attach($item2->id, ['quantity' => 10]);

        // Act
        $response = $service->export($product);

        // Assert
        $this->assertInstanceOf(StreamedResponse::class, $response);

        $headers = $response->headers->all();

        $this->assertArrayHasKey('content-type', $headers);
        $this->assertEquals('text/csv', $headers['content-type'][0]);

        $this->assertArrayHasKey('content-disposition', $headers);
        $this->assertStringContainsString('attachment; filename="product_' . $product->id . '_items.csv"', $headers['content-disposition'][0]);
    }
}
