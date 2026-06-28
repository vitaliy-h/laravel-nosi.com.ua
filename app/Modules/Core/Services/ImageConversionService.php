<?php

namespace App\Modules\Core\Services;

use Illuminate\Support\Facades\Storage;
use Throwable;

class ImageConversionService
{
    public function __construct(
        private ImageResizer $resizer,
    ) {}

    public function resolveSizeConfig(string $preset, string $originalPath): ?array
    {
        $entity = explode('/', $originalPath)[0];

        return config("image_sizes.{$preset}.{$entity}");
    }

    public function previewPath(string $preset, string $originalPath): string
    {
        return "{$preset}/{$originalPath}";
    }

    public function generate(string $originalPath, string $preset): void
    {
        if (! Storage::exists($originalPath)) {
            return;
        }

        $sizeConfig = $this->resolveSizeConfig($preset, $originalPath);

        if ($sizeConfig === null) {
            return;
        }

        $destPath = $this->previewPath($preset, $originalPath);

        if (Storage::exists($destPath)) {
            return;
        }

        $this->resizer->resize($originalPath, $destPath, $sizeConfig);
    }

    public function generateEager(string $originalPath): void
    {
        if ($originalPath === '') {
            return;
        }

        foreach (array_keys(config('image_sizes', [])) as $preset) {
            try {
                $this->generate($originalPath, $preset);
            } catch (Throwable $e) {
                report($e);
            }
        }
    }

    public function deleteForField(string $table, int|string $id, string $fieldSegment): void
    {
        foreach (array_keys(config('image_sizes', [])) as $preset) {
            $directory = "{$preset}/{$table}/{$id}/{$fieldSegment}";

            if (Storage::exists($directory)) {
                Storage::deleteDirectory($directory);
            }
        }
    }

    public function deleteForPath(string $originalPath): void
    {
        foreach (array_keys(config('image_sizes', [])) as $preset) {
            $previewPath = $this->previewPath($preset, $originalPath);

            if (Storage::exists($previewPath)) {
                Storage::delete($previewPath);
            }
        }
    }

    public function deleteForRecord(string $table, int|string $id): void
    {
        foreach (array_keys(config('image_sizes', [])) as $preset) {
            $directory = "{$preset}/{$table}/{$id}";

            if (Storage::exists($directory)) {
                Storage::deleteDirectory($directory);
            }
        }
    }
}
