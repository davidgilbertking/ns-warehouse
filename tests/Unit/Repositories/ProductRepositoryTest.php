<?php

namespace Tests\Unit\Repositories;

use App\Models\Product;
use App\Repositories\ProductRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ProductRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ProductRepository();
    }

    public function testAllReturnsAllProducts(): void
    {
        Product::factory()->count(3)->create();

        $products = $this->repository->all();

        $this->assertCount(3, $products);
        $this->assertInstanceOf(Product::class, $products->first());
    }

    public function testFindWithItemsReturnsProductWithItems(): void
    {
        $product = Product::factory()->create();

        $foundProduct = $this->repository->findWithItems($product->id);

        $this->assertNotNull($foundProduct);
        $this->assertEquals($product->id, $foundProduct->id);
    }
}
