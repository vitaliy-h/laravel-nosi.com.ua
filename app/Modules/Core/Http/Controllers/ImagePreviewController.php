<?php

namespace App\Modules\Core\Http\Controllers;

use App\Modules\Core\Services\ImageConversionService;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ImagePreviewController
{
    public function __invoke(
        string $preset,
        string $path,
        ImageConversionService $conversions
    ): BinaryFileResponse {
        abort_unless(
            array_key_exists($preset, config('image_sizes', [])),
            404,
            "Image preset [{$preset}] is not defined."
        );

        $previewPath = $conversions->previewPath($preset, $path);

        if (Storage::exists($previewPath)) {
            return response()->file(Storage::path($previewPath));
        }

        abort_unless(
            Storage::exists($path),
            404,
            "Original image not found."
        );

        $entity = explode('/', $path)[0];

        abort_unless(
            $conversions->resolveSizeConfig($preset, $path) !== null,
            404,
            "No size config for preset [{$preset}] and entity [{$entity}]."
        );

        $conversions->generate($path, $preset);

        return response()->file(Storage::path($previewPath));
    }
}
