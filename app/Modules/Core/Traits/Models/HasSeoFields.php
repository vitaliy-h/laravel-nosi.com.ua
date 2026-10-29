<?php

namespace App\Modules\Core\Traits\Models;

use Illuminate\Support\Str;

/**
 * slug, meta_title and meta_description of a public record.
 *
 * On every save the blank ones are filled: the slug from the title (Ukrainian
 * transliteration), meta_title / meta_description from seoMetaTitle() /
 * seoMetaDescription(), which a model overrides for its own wording (Category,
 * Product). The admin «generate_seo» toggle (on by default when creating) first
 * clears all three, so they are built anew. Filled-in values are never touched.
 */
trait HasSeoFields
{
    /** The longest meta description worth writing — Google shows about 160 characters. */
    protected int $seoDescriptionLength = 160;

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
        }

        $this->generateSeoFields();
    }

    protected function shouldGenerateSeo(): bool
    {
        return (bool) ($this->generate_seo ?? false);
    }

    protected function generateSeoFields(): void
    {
        $title = $this->getSeoTitle();

        if ($title === '') {
            return;
        }

        if (blank($this->slug)) {
            $this->generateSlug($title);
        }

        if (blank($this->meta_title)) {
            $this->meta_title = $this->seoMetaTitle();
        }

        if (blank($this->meta_description)) {
            $this->meta_description = $this->seoMetaDescription();
        }
    }

    /**
     * The <title> built when meta_title is left empty. Also the storefront's
     * fallback for a record saved before the rule existed.
     */
    public function seoMetaTitle(): string
    {
        return $this->getSeoTitle();
    }

    /**
     * The meta description built when it is left empty: the start of the text,
     * or the title when there is no text.
     */
    public function seoMetaDescription(): string
    {
        return $this->seoExcerpt($this->seoDescriptionLength) ?: $this->getSeoTitle();
    }

    protected function getSeoTitle(): string
    {
        return trim((string) data_get($this, $this->seoTitleField()));
    }

    /**
     * The content field as plain text, cut at a word to at most $length characters.
     */
    protected function seoExcerpt(int $length): string
    {
        $field = $this->seoContentField();
        $text = $field ? (string) data_get($this, $field) : '';
        $text = Str::squish(html_entity_decode(strip_tags(str_replace('<', ' <', $text)), ENT_QUOTES | ENT_HTML5));

        if ($length <= 1 || mb_strlen($text) <= $length) {
            return $length <= 1 ? '' : $text;
        }

        $cut = mb_substr($text, 0, $length - 1);
        $space = mb_strrpos($cut, ' ');

        return rtrim($space ? mb_substr($cut, 0, $space) : $cut, " ,.;:—-") . '…';
    }

    /**
     * «Жіночі шкарпетки» → «жіночі шкарпетки» after «Купити»; a word in capitals
     * (a brand: «DMDBS шкарпетки») keeps its case.
     */
    protected function seoLowerFirst(string $text): string
    {
        $second = mb_substr($text, 1, 1);

        return $second !== '' && $second === mb_strtoupper($second)
            ? $text
            : mb_strtolower(mb_substr($text, 0, 1)) . mb_substr($text, 1);
    }

    /** The shop's name for meta texts, as the header logo spells it («Nosi»). */
    protected function seoShopName(): string
    {
        return Str::ucfirst(t('header.logo') ?: (string) config('app.name'));
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
     * Generate unique slug. Ukrainian transliteration (ж → zh, ч → ch, х → kh,
     * и → y), the same as the category addresses typed by hand.
     */
    public function generateSlug($source): void
    {
        $baseSlug = Str::slug($source, '-', 'uk');
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
