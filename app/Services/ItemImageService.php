<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ItemImage;
use App\Models\Item;
use App\Repositories\ItemImageRepository;
use App\DTOs\ItemImageDTO;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ItemImageService
{
    public function __construct(
        protected ItemImageRepository $repository
    ) {}

    public function store(Item $item, string $uploadedFilePath): ItemImage
    {
        $thumbPath = $this->makeThumb($uploadedFilePath);

        return $item->images()->create([
            'path' => $uploadedFilePath,
            'thumb_path' => $thumbPath,
        ]);
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
            // 1) Save original
            $path = $image->store('items', 'public');

            // 2) Make thumbnail (WebP, width 1600px)
            $thumbPath = $this->makeThumb($path);

            // 3) Save to DB
            $item->images()->create([
                'path' => $path,
                'thumb_path' => $thumbPath,
            ]);
        }
    }

    private function makeThumb(string $originalPath): ?string
    {
        // originalPath like: items/abc.jpg (stored in disk "public")
        $source = Storage::disk('public')->path($originalPath);

        // Ensure source exists
        if (!is_file($source)) {
            return null;
        }

        // Put thumbs next to originals in separate folder
        $filename = pathinfo($originalPath, PATHINFO_FILENAME);
        $thumbRelative = 'items/thumbs/' . $filename . '.webp';
        $thumbAbsolute = Storage::disk('public')->path($thumbRelative);

        // Create target directory if missing
        $dir = dirname($thumbAbsolute);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $manager = new ImageManager(new Driver());

        // Read → resize → encode WebP
        $img = $manager->read($source);

        // Resize down only if wider than 1600
        if ($img->width() > 1600) {
            $img = $img->scaleDown(width: 1600);
        }

        // Save webp (quality ~75)
        $img->toWebp(quality: 75)->save($thumbAbsolute);

        return $thumbRelative;
    }
}
