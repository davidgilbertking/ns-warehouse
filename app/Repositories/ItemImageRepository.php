<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\ItemImage;
use App\Models\Item;
use App\DTOs\ItemImageDTO;

class ItemImageRepository
{
    public function create(Item $item, ItemImageDTO $data): ItemImage
    {
        return $item->images()->create([
                                           'path' => $data->getPath(),
                                       ]);
    }

    public function delete(ItemImage $image): bool
    {
        return $image->delete();
    }
}
