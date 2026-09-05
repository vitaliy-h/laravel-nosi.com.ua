<?php

namespace App\Modules\Core\Traits\Models;

use Illuminate\Support\Facades\Storage;

trait HasBuilderContent
{
    public static function bootHasBuilderContent(): void
    {
        static::created(function ($model) {
            $model->moveTempBuilderContent();
        });

        static::updating(function ($model) {
            $model->cleanRemovedBuilderImages();
        });

        static::deleted(function ($model) {
            $model->deleteBuilderDirectory();
        });
    }

    public function getBuilderContentDirectory(): string
    {
        return $this->getTable() . '/' . ($this->getKey() ?? 'temp') . '/content';
    }

    public function moveTempBuilderContent(): void
    {
        $content = $this->content;

        if (empty($content) || !is_array($content)) {
            return;
        }

        $tempPrefix = $this->getTable() . '/temp/';
        $realPrefix = $this->getTable() . '/' . $this->getKey() . '/';
        $tempContentRoot = $this->getTable() . '/temp/content';
        $movedDirs = [];
        $updated = false;

        foreach ($content as &$block) {
            $type = $block['type'] ?? null;

            if ($type === 'image' && !empty($block['data']['image'])) {
                $oldPath = $block['data']['image'];
                if ($this->moveBuilderFile($block['data']['image'], $tempPrefix, $realPrefix)) {
                    $movedDirs[] = dirname($oldPath);
                    $updated = true;
                }
            }

            if ($type === 'gallery' && !empty($block['data']['images'])) {
                foreach ($block['data']['images'] as &$img) {
                    $oldPath = $img;
                    if ($this->moveBuilderFile($img, $tempPrefix, $realPrefix)) {
                        $movedDirs[] = dirname($oldPath);
                        $updated = true;
                    }
                }
                unset($img);
            }
        }
        unset($block);

        if ($updated) {
            $this->content = $content;
            $this->saveQuietly();

            foreach (array_unique($movedDirs) as $dir) {
                $this->deleteEmptyParentDirs($dir, $tempContentRoot);
            }
        }
    }

    protected function cleanRemovedBuilderImages(): void
    {
        if (!$this->isDirty('content')) {
            return;
        }

        $orig = $this->getOriginal('content');
        $oldContent = is_string($orig) ? (json_decode($orig, true) ?? []) : ($orig ?? []);

        $oldPaths = $this->extractBuilderImagePaths($oldContent);
        $newPaths = $this->extractBuilderImagePaths($this->content ?? []);

        $contentRoot = $this->getTable() . '/' . $this->getKey() . '/content';

        foreach (array_diff($oldPaths, $newPaths) as $path) {
            Storage::disk('public')->delete($path);
            $this->deleteEmptyParentDirs(dirname($path), $contentRoot);
        }
    }

    private function deleteEmptyParentDirs(string $dir, string $stopAt): void
    {
        while ($dir !== $stopAt && str_starts_with($dir, $stopAt)) {
            if (!empty(Storage::disk('public')->files($dir)) ||
                !empty(Storage::disk('public')->directories($dir))) {
                break;
            }

            Storage::disk('public')->deleteDirectory($dir);
            $dir = dirname($dir);
        }
    }

    public function deleteBuilderDirectory(): void
    {
        Storage::disk('public')->deleteDirectory(
            $this->getTable() . '/' . $this->getKey()
        );
    }

    private function moveBuilderFile(string &$path, string $tempPrefix, string $realPrefix): bool
    {
        if (!str_starts_with($path, $tempPrefix)) {
            return false;
        }

        $newPath = $realPrefix . substr($path, strlen($tempPrefix));

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->move($path, $newPath);
            $path = $newPath;

            return true;
        }

        return false;
    }

    private function extractBuilderImagePaths(array $content): array
    {
        $paths = [];

        foreach ($content as $block) {
            $type = $block['type'] ?? null;
            $data = $block['data'] ?? [];

            if ($type === 'image' && !empty($data['image'])) {
                $paths[] = $data['image'];
            }

            if ($type === 'gallery' && !empty($data['images'])) {
                foreach ((array) $data['images'] as $img) {
                    $paths[] = $img;
                }
            }
        }

        return $paths;
    }
}
