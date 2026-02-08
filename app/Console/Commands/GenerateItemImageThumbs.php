<?php

namespace App\Console\Commands;

use App\Models\ItemImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class GenerateItemImageThumbs extends Command
{
    protected $signature = 'images:thumbs {--limit=0}';
    protected $description = 'Generate webp thumbnails for item images that have no thumb_path';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');

        $q = ItemImage::query()
                      ->whereNull('thumb_path')
                      ->orWhere('thumb_path', '');

        if ($limit > 0) {
            $q->limit($limit);
        }

        $images = $q->get();
        $total = $images->count();

        if ($total === 0) {
            $this->info('Nothing to do.');
            return self::SUCCESS;
        }

        $this->info("Found {$total} images without thumbs.");

        $manager = new ImageManager(new Driver());
        $done = 0;
        $failed = 0;

        foreach ($images as $imgModel) {
            try {
                $originalPath = $imgModel->path;
                $source = Storage::disk('public')->path($originalPath);

                if (!is_file($source)) {
                    $failed++;
                    $this->warn("Missing file: {$originalPath}");
                    continue;
                }

                $filename = pathinfo($originalPath, PATHINFO_FILENAME);
                $thumbRelative = 'items/thumbs/' . $filename . '.webp';
                $thumbAbsolute = Storage::disk('public')->path($thumbRelative);

                $dir = dirname($thumbAbsolute);
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }

                $image = $manager->read($source);

                if ($image->width() > 1600) {
                    $image = $image->scaleDown(width: 1600);
                }

                $image->toWebp(quality: 75)->save($thumbAbsolute);

                $imgModel->thumb_path = $thumbRelative;
                $imgModel->save();

                $done++;
            } catch (\Throwable $e) {
                $failed++;
                $this->warn("Failed for ID {$imgModel->id}: " . $e->getMessage());
            }
        }

        $this->info("Done: {$done}, failed: {$failed}");

        return self::SUCCESS;
    }
}
