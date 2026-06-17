<?php

namespace App\Modules\Core\Http\Controllers;

use App\Modules\Core\Services\ImageResizer;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ImagePreviewController
{
    public function __invoke(
        string $preset,
        string $path,
        ImageResizer $resizer
    ): BinaryFileResponse {
        abort_unless(
            array_key_exists($preset, config('image_sizes', [])),
            404,
            "Image preset [{$preset}] is not defined."
        );

        $previewPath = "{$preset}/{$path}";

        if (Storage::exists($previewPath)) {
            return response()->file(Storage::path($previewPath));
        }

        abort_unless(
            Storage::exists($path),
            404,
            "Original image not found."
        );

        $entity = explode('/', $path)[0];

        $sizeConfig = config("image_sizes.{$preset}.{$entity}");

        abort_unless(
            $sizeConfig !== null,
            404,
            "No size config for preset [{$preset}] and entity [{$entity}]."
        );

        $resizer->resize($path, $previewPath, $sizeConfig);

        return response()->file(Storage::path($previewPath));
    }
}
