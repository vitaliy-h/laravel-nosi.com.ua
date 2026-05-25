<?php

namespace App\Modules\Core\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HasImage
{
    private array $imgFields = ['img'];
    public function initializeHasImage(): void
    {
        $this->appends = array_unique(array_merge(
            $this->appends ?? [],
            ['image']
        ));
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

    /**
     * Normalize filename
     */
    protected static function normalizeFilename(string $filename): string
    {
        $ext = pathinfo($filename, PATHINFO_EXTENSION);

        $name = Str::uuid();

        return $ext ? "{$name}.{$ext}" : $name;
    }

    public function moveTempFile(): void
    {
        $fields = $this->imgFields;

        foreach ($fields as $field) {
            $oldPath = $this->{$field};

            if (
                !$oldPath ||
                !Storage::exists($oldPath) ||
                !str_contains($oldPath, '/temp/')
            ) {
                continue;
            }

            $filename = basename($oldPath);

            $newPath = $this->getFilePath(
                $filename,
                $field,
            );

            Storage::move($oldPath, $newPath);

            $this->{$field} = $newPath;
        }

        $this->saveQuietly();
    }

    /**
     * Delete model files directory
     */
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

    /**
     * Delete old images on update
     */
    protected function deleteOldImages(): void
    {
        $fields = $this->imgFields;

        foreach ($fields as $field) {
            if (!$this->isDirty($field)) {
                continue;
            }

            $oldPath = $this->getOriginal($field);

            if (
                $oldPath &&
                Storage::exists($oldPath)
            ) {
                Storage::delete($oldPath);
            }
        }
    }
}
