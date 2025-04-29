<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\DTOs\ProductStoreDTO;
use App\DTOs\ProductUpdateDTO;
use App\Models\Product;
use App\Repositories\ItemRepository;
use App\Repositories\ProductRepository;
use App\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    use RefreshDatabase;

    public function testCreateProductSuccessfully(): void
    {
        $productRepository = Mockery::mock(ProductRepository::class);
        $itemRepository = Mockery::mock(ItemRepository::class);

        $service = new ProductService($productRepository, $itemRepository);

        $dto = new ProductStoreDTO(
            name: 'New Product',
            items: [
                      ['id' => 1, 'quantity' => 5],
                      ['id' => 2, 'quantity' => 3],
                  ]
        );

        $product = Product::factory()->make();

        $productRepository->shouldReceive('create')
                          ->once()
                          ->with($dto)
                          ->andReturn($product);

        $productRepository->shouldReceive('attachItems')
                          ->once()
                          ->with($product, $dto->items);

        Auth::shouldReceive('check')->andReturn(false);

        $result = $service->createProduct($dto);

        $this->assertSame($product, $result);
    }

    public function testUpdateProductSuccessfully(): void
    {
        $productRepository = Mockery::mock(ProductRepository::class);
        $itemRepository = Mockery::mock(ItemRepository::class);

        $service = new ProductService($productRepository, $itemRepository);

        $product = Product::factory()->make();

        $dto = new ProductUpdateDTO(
            name: 'Updated Product',
            items: [
                      ['id' => 1, 'quantity' => 2]
                  ]
        );

        $productRepository->shouldReceive('update')
                          ->once()
                          ->with($product, $dto);

        $productRepository->shouldReceive('syncItems')
                          ->once()
                          ->with($product, $dto->items);

        Auth::shouldReceive('check')->andReturn(false);

        $service->updateProduct($product, $dto);

        $this->assertTrue(true);
    }

    public function testDeleteProductSuccessfully(): void
    {
        $productRepository = Mockery::mock(ProductRepository::class);
        $itemRepository = Mockery::mock(ItemRepository::class);

        $service = new ProductService($productRepository, $itemRepository);

        $product = Product::factory()->make();

        $productRepository->shouldReceive('delete')
                          ->once()
                          ->with($product);

        Auth::shouldReceive('check')->andReturn(false);

        $service->deleteProduct($product);

        $this->assertTrue(true);
    }
}
