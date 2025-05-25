<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemVideo;
use App\Http\Requests\ItemVideoStoreRequest;
use App\Services\ItemVideoService;

class ItemVideoController extends Controller
{
    public function __construct(
        protected ItemVideoService $service
    ) {}

    public function store(ItemVideoStoreRequest $request, Item $item)
    {
        $videos = $request->file('videos');

        if (is_array($videos)) {
            foreach ($videos as $video) {
                $path = $video->store('item_videos', 'public');
                $this->service->store($item, $path);
            }
        }

        return redirect()->route('items.edit', $item)->with('success', 'Видео загружено.');
    }

    public function destroy(ItemVideo $video)
    {
        $item = $video->item;
        $this->service->destroy($video);

        return redirect()->route('items.edit', $item)->with('success', 'Видео удалено.');
    }
}
