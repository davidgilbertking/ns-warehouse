<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Item;
use App\Models\ItemVideo;
use App\DTOs\ItemVideoDTO;

class ItemVideoRepository
{
    public function create(Item $item, ItemVideoDTO $data): ItemVideo
    {
        return $item->videos()->create([
                                           'path' => $data->getPath(),
                                       ]);
    }

    public function delete(ItemVideo $video): bool
    {
        return $video->delete();
    }
}
