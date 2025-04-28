<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ItemImage;
use App\Models\Item;
use App\Repositories\ItemImageRepository;
use App\DTOs\ItemImageDTO;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;


class ItemImageService
{
    public function __construct(
        protected ItemImageRepository $repository
    ) {}

    public function store(Item $item, string $uploadedFilePath): ItemImage
    {
        $dto = new ItemImageDTO($uploadedFilePath);
        return $this->repository->create($item, $dto);
    }

    public function destroy(ItemImage $image): void
    {
        Storage::disk('public')->delete($image->path);
        $this->repository->delete($image);
    }

    /**
     * @param Item $item
     * @param UploadedFile[] $images
     */
    public function uploadImages(Item $item, array $images): void
    {
        foreach ($images as $image) {
            $path = $image->store('items', 'public');
            $item->images()->create([
                                        'path' => $path,
                                    ]);
        }
    }
}
