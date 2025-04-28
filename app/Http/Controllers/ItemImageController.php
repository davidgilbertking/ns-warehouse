<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ItemImageService;
use App\Http\Requests\ItemImageStoreRequest;
use App\Models\Item;
use App\Models\ItemImage;

class ItemImageController extends Controller
{
    public function __construct(
        protected ItemImageService $service
    ) {}

    public function store(ItemImageStoreRequest $request, Item $item)
    {
        $path = $request->file('image')->store('items', 'public');
        $this->service->store($item, $path);

        return redirect()->route('items.edit', $item)->with('success', 'Фото загружено.');
    }

    public function destroy(ItemImage $image)
    {
        $item = $image->item;

        $this->service->destroy($image);

        return redirect()->route('items.edit', $item)->with('success', 'Фото удалено.');
    }
}
