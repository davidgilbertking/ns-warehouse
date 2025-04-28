<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\ProductStoreDTO;
use App\DTOs\ProductUpdateDTO;
use App\Models\Product;
use App\Repositories\ProductRepository;
use App\Repositories\ItemRepository;
use App\Models\ActivityLog;

class ProductService
{
    public function __construct(
        protected ProductRepository $productRepository,
        protected ItemRepository    $itemRepository,
    ) {
    }

    public function getPaginatedProducts(int $perPage = 10)
    {
        return $this->productRepository->paginateWithItemCount($perPage);
    }

    public function getCreateData(): array
    {
        $items = $this->itemRepository->all();
        return compact('items');
    }

    public function createProduct(ProductStoreDTO $dto): Product
    {
        $product = $this->productRepository->create($dto);

        if (!empty($dto->items)) {
            $this->productRepository->attachItems($product, $dto->items);
        }

        $this->logAction('created_product', $product);

        return $product;
    }

    public function updateProduct(Product $product, ProductUpdateDTO $dto): void
    {
        $this->productRepository->update($product, $dto);

        $this->productRepository->syncItems($product, $dto->items ?? []);

        $this->logAction('updated_product', $product);
    }

    public function deleteProduct(Product $product): void
    {
        $this->productRepository->delete($product);

        $this->logAction('deleted_product', $product);
    }

    protected function logAction(string $action, Product $product): void
    {
        if (auth()->check()) {
            ActivityLog::create([
                                    'user_id'     => auth()->id(),
                                    'action'      => $action,
                                    'entity_type' => 'Product',
                                    'entity_id'   => $product->id,
                                    'description' => ucfirst(str_replace('_', ' ', $action)) . ": {$product->name}",
                                ]);
        }
    }
}
