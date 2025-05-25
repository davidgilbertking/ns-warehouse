<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Item;
use App\Models\ItemVideo;
use App\Repositories\ItemVideoRepository;
use App\DTOs\ItemVideoDTO;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class ItemVideoService
{
    public function __construct(
        protected ItemVideoRepository $repository
    ) {}

    public function store(Item $item, string $uploadedFilePath): ItemVideo
    {
        $dto = new ItemVideoDTO($uploadedFilePath);
        return $this->repository->create($item, $dto);
    }

    public function destroy(ItemVideo $video): void
    {
        Storage::disk('public')->delete($video->path);
        $this->repository->delete($video);
    }

    /**
     * @param Item $item
     * @param UploadedFile[] $videos
     */
    public function uploadVideos(Item $item, array $videos): void
    {
        foreach ($videos as $video) {
            $path = $video->store('videos', 'public');
            $item->videos()->create([
                                        'path' => $path,
                                    ]);
        }
    }
}
