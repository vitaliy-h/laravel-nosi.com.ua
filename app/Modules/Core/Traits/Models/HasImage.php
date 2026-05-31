<?php

namespace App\Modules\Core\Traits\Models;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HasImage
{
    protected array $imgFields = ['img'];
    protected array $imgArrayFields = [];

    public function initializeHasImage(): void
    {
        $appends = ['image'];

        if (!empty($this->imgArrayFields)) {
            $appends[] = 'images';
        }

        $this->appends = array_unique(array_merge($this->appends ?? [], $appends));
    }

    public static function bootHasImage(): void
    {
        static::created(function ($model) {
            $model->moveTempFile();
        });
        static::updating(function ($model) {
            $model->deleteOldImages();
        });
        static::deleted(function ($model) {
            $model->deleteFilesDirectory();
        });
    }

    public function getFilePath(string $filename, string $field = 'img'): string
    {
        $table = $this->getTable();
        $id = $this->getKey() ?? 'temp';
        $filename = self::normalizeFilename($filename);

        return "{$table}/{$id}/{$field}/{$filename}";
    }

    protected static function normalizeFilename(string $filename): string
    {
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $name = Str::uuid();

        return $ext ? "{$name}.{$ext}" : $name;
    }

    public function moveTempFile(): void
    {
        foreach ($this->imgFields as $field) {
            $oldPath = $this->{$field};

            if (
                !$oldPath ||
                !Storage::exists($oldPath) ||
                !str_contains($oldPath, '/temp/')
            ) {
                continue;
            }

            $newPath = $this->getFilePath(basename($oldPath), $field);
            Storage::move($oldPath, $newPath);
            $this->{$field} = $newPath;
        }

        foreach ($this->imgArrayFields as $field) {
            $paths = $this->{$field};

            if (empty($paths) || !is_array($paths)) {
                continue;
            }

            $updated = false;

            foreach ($paths as $i => $oldPath) {
                if (
                    !$oldPath ||
                    !Storage::exists($oldPath) ||
                    !str_contains($oldPath, '/temp/')
                ) {
                    continue;
                }

                $newPath = $this->getFilePath(basename($oldPath), $field);
                Storage::move($oldPath, $newPath);
                $paths[$i] = $newPath;
                $updated = true;
            }

            if ($updated) {
                $this->{$field} = $paths;
            }
        }

        $this->saveQuietly();
    }

    public function deleteFilesDirectory(): void
    {
        $directory = "{$this->getTable()}/{$this->getKey()}";

        if (Storage::exists($directory)) {
            Storage::deleteDirectory($directory);
        }
    }

    public function getImageAttribute(): string
    {
        if (!empty($this->img)) {
            return Storage::url($this->img);
        }

        return asset('/assets/images/no_image.svg');
    }

    public function getImagesAttribute(): array
    {
        if (empty($this->imgs) || !is_array($this->imgs)) {
            return [];
        }

        return array_map(fn($path) => Storage::url($path), $this->imgs);
    }

    protected function deleteOldImages(): void
    {
        foreach ($this->imgFields as $field) {
            if (!$this->isDirty($field)) {
                continue;
            }

            $oldPath = $this->getOriginal($field);

            if ($oldPath && Storage::exists($oldPath)) {
                Storage::delete($oldPath);
            }
        }

        foreach ($this->imgArrayFields as $field) {
            if (!$this->isDirty($field)) {
                continue;
            }

            $oldPaths = $this->getOriginal($field);

            if (is_string($oldPaths)) {
                $oldPaths = json_decode($oldPaths, true) ?? [];
            }

            $newPaths = $this->{$field} ?? [];
            $removed = array_diff($oldPaths ?: [], $newPaths);

            foreach ($removed as $path) {
                if ($path && Storage::exists($path)) {
                    Storage::delete($path);
                }
            }
        }
    }
}
