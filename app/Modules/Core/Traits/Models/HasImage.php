<?php

namespace App\Modules\Core\Traits\Models;

use App\Modules\Core\Services\ImageConversionService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HasImage
{
    /** @var array<int, list<string>> */
    private static array $pendingEagerImagePaths = [];

    public function initializeHasImage(): void
    {
        $appends = ['image'];

        if (!empty($this->getImgArrayFields())) {
            $appends[] = 'images';
        }

        $this->appends = array_unique(array_merge($this->appends ?? [], $appends));
    }

    public static function bootHasImage(): void
    {
        static::created(function ($model) {
            $model->moveTempFile();
            $model->generateEagerConversions($model->collectImagePaths());
        });
        static::updating(function ($model) {
            $model->deleteOldImages();
            static::$pendingEagerImagePaths[spl_object_id($model)] = $model->collectDirtyImagePaths();
        });
        static::updated(function ($model) {
            $objectId = spl_object_id($model);
            $paths = static::$pendingEagerImagePaths[$objectId] ?? [];
            unset(static::$pendingEagerImagePaths[$objectId]);

            if ($paths !== []) {
                $model->generateEagerConversions($paths);
            }
        });
        static::deleted(function ($model) {
            $model->deleteFilesDirectory();
            $model->deleteConversionsForRecord();
        });
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

        return array_map(static fn ($path) => Storage::url($path), $this->imgs);
    }

    public function imageUrl(string $preset): string
    {
        if (empty($this->img)) {
            return asset('/assets/images/no_image.svg');
        }

        return image_url($this->img, $preset);
    }

    protected function getImgFields(): array
    {
        return property_exists($this, 'imgFields') ? $this->imgFields : ['img'];
    }

    protected function getImgArrayFields(): array
    {
        return property_exists($this, 'imgArrayFields') ? $this->imgArrayFields : [];
    }

    protected function getImgArrayPathFields(): array
    {
        return property_exists($this, 'imgArrayPathFields') ? $this->imgArrayPathFields : [];
    }

    protected function getPathSegmentForField(string $field): string
    {
        return $this->getImgArrayPathFields()[$field] ?? $field;
    }

    protected function collectImagePaths(): array
    {
        $paths = [];

        foreach ($this->getImgFields() as $field) {
            $path = $this->{$field};

            if ($path) {
                $paths[] = $path;
            }
        }

        foreach ($this->getImgArrayFields() as $field) {
            $fieldPaths = $this->{$field};

            if (empty($fieldPaths) || !is_array($fieldPaths)) {
                continue;
            }

            foreach ($fieldPaths as $path) {
                if ($path) {
                    $paths[] = $path;
                }
            }
        }

        return $paths;
    }

    protected function collectDirtyImagePaths(): array
    {
        $paths = [];

        foreach ($this->getImgFields() as $field) {
            if (!$this->isDirty($field)) {
                continue;
            }

            $path = $this->{$field};

            if ($path) {
                $paths[] = $path;
            }
        }

        foreach ($this->getImgArrayFields() as $field) {
            if (!$this->isDirty($field)) {
                continue;
            }

            $newPaths = $this->{$field} ?? [];

            if (!is_array($newPaths)) {
                continue;
            }

            $oldPaths = $this->getOriginal($field);

            if (is_string($oldPaths)) {
                $oldPaths = json_decode($oldPaths, true) ?? [];
            }

            foreach (array_diff($newPaths, $oldPaths ?: []) as $path) {
                if ($path) {
                    $paths[] = $path;
                }
            }
        }

        return $paths;
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
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $name = Str::slug(pathinfo($filename, PATHINFO_FILENAME));

        if (empty($name)) {
            $name = (string) Str::uuid();
        }

        return $ext ? "{$name}.{$ext}" : $name;
    }

    private function moveTempFile(): void
    {
        $movedDirs = [];

        foreach ($this->getImgFields() as $field) {
            $oldPath = $this->{$field};

            if (!$oldPath || !Storage::exists($oldPath) || !str_contains($oldPath, '/temp/')) {
                continue;
            }

            $newPath = $this->getFilePath(basename($oldPath), $field);
            Storage::move($oldPath, $newPath);
            $this->{$field} = $newPath;
            $movedDirs[] = dirname($oldPath);
        }

        foreach ($this->getImgArrayFields() as $field) {
            $paths = $this->{$field};

            if (empty($paths) || !is_array($paths)) {
                continue;
            }

            $updated = false;

            foreach ($paths as $i => $oldPath) {
                if (!$oldPath || !Storage::exists($oldPath) || !str_contains($oldPath, '/temp/')) {
                    continue;
                }

                $newPath = $this->getFilePath(basename($oldPath), $this->getPathSegmentForField($field));
                Storage::move($oldPath, $newPath);
                $paths[$i] = $newPath;
                $movedDirs[] = dirname($oldPath);
                $updated = true;
            }

            if ($updated) {
                $this->{$field} = $paths;
            }
        }

        $this->saveQuietly();

        $tempRoot = $this->getTable() . '/temp';

        foreach (array_unique($movedDirs) as $dir) {
            $this->deleteEmptyParentDirs($dir, $tempRoot);
        }
    }

    private function deleteFilesDirectory(): void
    {
        $directory = $this->getTable() . '/' . $this->getKey();

        if (Storage::exists($directory)) {
            Storage::deleteDirectory($directory);
        }
    }

    private function deleteOldImages(): void
    {
        $recordRoot = $this->getTable() . '/' . $this->getKey();

        foreach ($this->getImgFields() as $field) {
            if (!$this->isDirty($field)) {
                continue;
            }

            $oldPath = $this->getOriginal($field);

            if ($oldPath) {
                $this->deleteConversionsForPath($oldPath);
            }

            if ($oldPath && Storage::exists($oldPath)) {
                Storage::delete($oldPath);
                $this->deleteEmptyParentDirs(dirname($oldPath), $recordRoot);
            }
        }

        foreach ($this->getImgArrayFields() as $field) {
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
                if ($path) {
                    $this->deleteConversionsForPath($path);
                }

                if ($path && Storage::exists($path)) {
                    Storage::delete($path);
                    $this->deleteEmptyParentDirs(dirname($path), $recordRoot);
                }
            }
        }
    }

    private function deleteConversionsForPath(?string $path): void
    {
        if ($path === null || $path === '') {
            return;
        }

        app(ImageConversionService::class)->deleteForPath($path);
    }

    private function deleteConversionsForRecord(): void
    {
        app(ImageConversionService::class)->deleteForRecord(
            $this->getTable(),
            $this->getKey(),
        );
    }

    private function deleteEmptyParentDirs(string $dir, string $stopAt): void
    {
        while ($dir !== $stopAt && str_starts_with($dir, $stopAt)) {
            if (!empty(Storage::files($dir)) || !empty(Storage::directories($dir))) {
                break;
            }

            Storage::deleteDirectory($dir);
            $dir = dirname($dir);
        }
    }

    private function isScalarImagePath(string $path): bool
    {
        $segments = explode('/', $path);

        if (count($segments) < 4) {
            return false;
        }

        $fieldSegment = $segments[2];

        return in_array($fieldSegment, $this->getImgFields(), true);
    }

    private function generateEagerConversions(array $paths): void
    {
        $service = app(ImageConversionService::class);

        foreach ($paths as $path) {
            if ($this->isScalarImagePath($path)) {
                $segments = explode('/', $path);
                $service->deleteForField($segments[0], $segments[1], $segments[2]);
            }

            $service->generateEager($path);
        }
    }
}
