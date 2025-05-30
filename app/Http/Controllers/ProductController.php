<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Services\ProductService;
use App\Services\ProductExportService;
use App\DTOs\ProductStoreDTO;
use App\DTOs\ProductUpdateDTO;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        protected ProductService       $service,
        protected ProductExportService $exportService,
    ) {
    }

    public function index()
    {
        $products = $this->service->getPaginatedProducts();

        return view('products.index', compact('products'));
    }

    public function create()
    {
        $data = $this->service->getCreateData();

        return view('products.create', $data);
    }

    public function store(ProductStoreRequest $request)
    {
        $dto = ProductStoreDTO::fromArray($request->validated());

        $this->service->createProduct($dto);

        return redirect()->route('products.index')->with('success', 'Тэг создан!');
    }

    public function show(Product $product)
    {
        $product->load('items');
        $allItems = $this->service->getCreateData()['items'];

        return view('products.show', compact('product', 'allItems'));
    }

    public function edit(Product $product)
    {
        $data = $this->service->getCreateData();

        $selectedItems = $product->items->map(function ($item) {
            return [
                'id'       => $item->id,
                'quantity' => $item->pivot->quantity,
            ];
        });

        return view(
            'products.edit', array_merge(
            $data,
            compact('product', 'selectedItems')
        )
        );
    }

    public function update(ProductUpdateRequest $request, Product $product)
    {
        $dto = ProductUpdateDTO::fromArray($request->validated());

        $this->service->updateProduct($product, $dto);

        return redirect()->route('products.show', $product)->with('success', 'Тэг обновлён!');
    }

    public function destroy(Product $product)
    {
        $this->service->deleteProduct($product);

        return redirect()->route('products.index')->with('success', 'Тэг удалён!');
    }

    public function export(Product $product)
    {
        return $this->exportService->export($product);
    }

    public function items(Product $product)
    {
        $items = $product->items->map(function ($item) {
            return [
                'id'       => $item->id,
                'name'     => $item->name,
                'quantity' => $item->pivot->quantity,
            ];
        });

        return response()->json(['items' => $items]);
    }
}
