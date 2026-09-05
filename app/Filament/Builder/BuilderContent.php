<?php

namespace App\Filament\Builder;

use Closure;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BuilderContent
{
    public static function directoryClosure(string $slot): Closure
    {
        return function ($component, $record) use ($slot) {
            $table = $record
                ? $record->getTable()
                : $component->getModelInstance()->getTable();
            $id = $record?->getKey() ?? 'temp';
            $parts = explode('.', $component->getStatePath());
            $uuidIndex = collect($parts)->search(fn ($p) => Str::isUuid($p));
            $blockUuid = $uuidIndex !== false ? $parts[$uuidIndex] : null;
            $field = $uuidIndex > 0 ? $parts[$uuidIndex - 1] : 'content';

            $basePath = "{$table}/{$id}/{$field}";
            $mapPath = "{$basePath}/.block-map.json";

            $map = [];
            if (Storage::disk('public')->exists($mapPath)) {
                $map = json_decode(Storage::disk('public')->get($mapPath), true) ?? [];
            }

            if ($blockUuid && isset($map[$blockUuid])) {
                $blockIndex = $map[$blockUuid];
            } else {
                $existing = collect(Storage::disk('public')->directories($basePath))
                    ->map(fn ($dir) => (int) basename($dir))
                    ->filter(fn ($n) => $n > 0);
                $blockIndex = ($existing->max() ?? 0) + 1;

                if ($blockUuid) {
                    $map[$blockUuid] = $blockIndex;
                    Storage::disk('public')->put($mapPath, json_encode($map));
                }
            }

            return "{$basePath}/{$blockIndex}/{$slot}";
        };
    }

    public static function fileNameClosure(): Closure
    {
        return function ($file) {
            $stem = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
            $ext = strtolower($file->getClientOriginalExtension());

            return $stem . '.' . $ext;
        };
    }
}
