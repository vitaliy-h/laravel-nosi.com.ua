<?php

namespace App\Modules\Core\Services;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class ImageResizer
{
    /**
     * Resize the original image at $sourcePath and save to $destPath.
     *
     * @param  string  $sourcePath  Relative path on default disk (e.g. shop_products/1/img/product.jpg)
     * @param  string  $destPath    Relative path for the preview (e.g. thumb/shop_products/1/img/product.jpg)
     * @param  array   $config      Preset config: ['width' => int, 'height' => int|null, 'mode' => 'crop'|'contain']
     */
    public function resize(string $sourcePath, string $destPath, array $config): void
    {
        $imageData = Storage::get($sourcePath);

        $image = Image::decode($imageData);

        if ($config['mode'] === 'crop') {
            $image->cover($config['width'], $config['height']);
        } else {
            $image->scaleDown($config['width'], $config['height'] ?? null);
        }

        $destDir = dirname($destPath);
        Storage::makeDirectory($destDir);

        Storage::put($destPath, (string) $image->encode());
    }
}
