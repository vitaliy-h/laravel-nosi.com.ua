<?php

namespace App\Filament\Support;

use Closure;
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
            $blockUuid = collect($parts)->first(fn($p) => Str::isUuid($p)) ?? Str::uuid();

            return "{$table}/{$id}/content/{$blockUuid}/{$slot}";
        };
    }

    public static function fileNameClosure(bool $withUuidPrefix = false): Closure
    {
        return function ($file) use ($withUuidPrefix) {
            $stem = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
            $ext = strtolower($file->getClientOriginalExtension());

            return $withUuidPrefix
                ? Str::uuid() . '/' . $stem . '.' . $ext
                : $stem . '.' . $ext;
        };
    }
}
