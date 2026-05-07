<?php

namespace App\Modules\Core\Traits;

use Illuminate\Support\Str;

trait HasSlug
{
    /**
     * Boot trait
     */
    public static function bootHasSlug(): void
    {
        static::creating(function ($model) {
            $model->generateSlug();
        });

        static::saving(function ($model) {
            if (!$model->slug) {
                $model->generateSlug();
            }
        });
    }

    /**
     * Generate unique slug
     */
    public function generateSlug(): void
    {
        $source = $this->slug_source_field ?? 'title';

        $baseSlug = Str::slug($this->{$source});
        $slug = $baseSlug;

        $i = 1;

        while ($this->slugExists($slug)) {
            $slug = $baseSlug . '-' . $i;
            $i++;
        }

        $this->slug = $slug;
    }

    /**
     * Check if slug exists in DB
     */
    protected function slugExists(string $slug): bool
    {
        $query = static::query()->where('slug', $slug);

        if ($this->exists) {
            $query->where('id', '!=', $this->id);
        }

        return $query->exists();
    }
}
