<?php

namespace App\Modules\Core\Traits\Models;

use App\Modules\Core\Services\LogService;

/**
 * Deletes the relations listed in $cascadeDeletes when the model is deleted.
 *
 * Children are deleted one by one through Eloquent rather than in a single
 * bulk query, so their own model events still fire — that is what keeps the
 * journal (see docs/starter/logs.md) and any child observers honest, and what
 * makes the cascade recursive for a child that uses this trait too.
 */
trait CascadesDeletes
{
    public static function bootCascadesDeletes(): void
    {
        static::deleting(function ($model): void {
            if ($model->getCascadeDeletes() === []) {
                return;
            }

            app(LogService::class)->batch(function () use ($model): void {
                foreach ($model->getCascadeDeletes() as $relation) {
                    $model->{$relation}()->each(fn ($child) => $child->delete());
                }
            });
        });
    }

    public function getCascadeDeletes(): array
    {
        return $this->cascadeDeletes ?? [];
    }
}
