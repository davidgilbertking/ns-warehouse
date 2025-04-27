<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Item;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProductController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $products = Product::withCount('items')->paginate(10);
        return view('products.index', compact('products'));
    }

    public function create()
    {
        $items = Item::all();
        return view('products.create', compact('items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
                                            'name' => 'required|string|max:255',
                                            'items' => 'nullable|array',
                                            'items.*.id' => 'exists:items,id',
                                            'items.*.quantity' => 'integer|min:1',
                                        ]);

        $product = Product::create([
                                       'name' => $validated['name'],
                                   ]);

        if (!empty($validated['items'])) {
            $product->items()->attach(
                collect($validated['items'])->mapWithKeys(function ($item) {
                    return [$item['id'] => ['quantity' => $item['quantity']]];
                })->toArray()
            );
        }

        ActivityLog::create([
                                'user_id' => auth()->id(),
                                'action' => 'created_product',
                                'entity_type' => 'Product',
                                'entity_id' => $product->id,
                                'description' => "Создан продукт: {$product->name}",
                            ]);

        return redirect()->route('products.index')->with('success', 'Продукт создан!');
    }

    public function show(Product $product)
    {
        $product->load('items');
        $allItems = Item::all();
        return view('products.show', compact('product', 'allItems'));
    }

    public function edit(Product $product)
    {
        $items = Item::all();
        $selectedItems = $product->items->map(function ($item) {
            return [
                'id' => $item->id,
                'quantity' => $item->pivot->quantity,
            ];
        });
        return view('products.edit', compact('product', 'items', 'selectedItems'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
                                            'name' => 'required|string|max:255',
                                            'items' => 'nullable|array',
                                            'items.*.id' => 'exists:items,id',
                                            'items.*.quantity' => 'integer|min:1',
                                        ]);

        $product->update([
                             'name' => $validated['name'],
                         ]);

        $product->items()->detach();

        if (!empty($validated['items'])) {
            $product->items()->attach(
                collect($validated['items'])->mapWithKeys(function ($item) {
                    return [$item['id'] => ['quantity' => $item['quantity']]];
                })->toArray()
            );
        }

        ActivityLog::create([
                                'user_id' => auth()->id(),
                                'action' => 'updated_product',
                                'entity_type' => 'Product',
                                'entity_id' => $product->id,
                                'description' => "Изменен продукт: {$product->name}",
                            ]);

        return redirect()->route('products.show', $product)->with('success', 'Продукт обновлён!');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        ActivityLog::create([
                                'user_id' => auth()->id(),
                                'action' => 'deleted_product',
                                'entity_type' => 'Product',
                                'entity_id' => $product->id,
                                'description' => "Удален продукт: {$product->name}",
                            ]);

        return redirect()->route('products.index')->with('success', 'Продукт удалён!');
    }

    public function export(Product $product)
    {
        $filename = 'product_' . $product->id . '_items.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($product) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Название предмета', 'Количество в продукте']);

            foreach ($product->items as $item) {
                fputcsv($handle, [
                    $item->name,
                    $item->pivot->quantity,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function items(Product $product)
    {
        $items = $product->items->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'quantity' => $item->pivot->quantity,
            ];
        });

        return response()->json(['items' => $items]);
    }
}
