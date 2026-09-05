<?php

namespace App\Modules\Core\Traits\Models;

use Illuminate\Support\Str;

trait HasSeoFields
{
    public function initializeHasSeoFields(): void
    {
        $this->fillable[] = 'generate_seo';
    }

    /**
     * Boot trait
     */
    public static function bootHasSeoFields(): void
    {
        static::saving(function ($model) {
            $model->handleSeo();
            unset($model->generate_seo);
        });
    }

    protected function handleSeo(): void
    {
        if ($this->shouldGenerateSeo()) {
            $this->slug = null;
            $this->meta_title = null;
            $this->meta_description = null;
            $this->generateSeoFields();
        }
    }

    protected function shouldGenerateSeo(): bool
    {
        return (bool) ($this->generate_seo ?? false);
    }

    protected function generateSeoFields(): void
    {
        $title = $this->getSeoTitle();
        $description = $this->getSeoDescription();

        if (blank($this->slug)) {
            $this->generateSlug($title);
        }

        if (blank($this->meta_title)) {
            $this->meta_title = $title;
        }

        if (blank($this->meta_description)) {
            $this->meta_description = strip_tags($description);
        }
    }

    protected function getSeoTitle(): string
    {
        return (string) data_get($this, $this->seoTitleField());
    }

    protected function getSeoDescription(): string
    {
        $field = $this->seoContentField();

        return $field
            ? (string) data_get($this, $field)
            : $this->getSeoTitle();
    }

    protected function seoTitleField(): string
    {
        return property_exists($this, 'seoTitleField')
            ? $this->seoTitleField
            : 'title';
    }

    protected function seoContentField(): ?string
    {
        return property_exists($this, 'seoContentField')
            ? $this->seoContentField
            : 'text';
    }

    /**
     * Generate unique slug
     */
    public function generateSlug($source): void
    {
        $baseSlug = Str::slug($source);
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
