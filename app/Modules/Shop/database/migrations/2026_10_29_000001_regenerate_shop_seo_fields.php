<?php

use App\Modules\Shop\Models\Category;
use App\Modules\Shop\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Migrations\Migration;

/**
 * Categories and products saved before the SEO rule (HasSeoFields::seoMetaTitle /
 * seoMetaDescription) got a meta title equal to the plain title and a placeholder
 * description («X — купити в інтернет-магазині Nosi.»), or none. Those are rebuilt;
 * texts written by hand are kept. Updated through the query builder — no model events.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach ([Category::class, Product::class] as $class) {
            $class::query()->with($class === Product::class ? ['brand'] : [])->each(function (Model $record): void {
                $changes = [];

                if (blank($record->meta_title) || trim($record->meta_title) === trim((string) $record->title)) {
                    $changes['meta_title'] = $record->seoMetaTitle();
                }

                if (blank($record->meta_description) || str_ends_with(trim($record->meta_description), 'купити в інтернет-магазині Nosi.')
                    || str_ends_with(trim($record->meta_description), 'купити в інтернет-магазині Nosi з доставкою по Україні.')) {
                    $changes['meta_description'] = $record->seoMetaDescription();
                }

                if ($changes !== []) {
                    $record->newQuery()->whereKey($record->getKey())->update($changes);
                }
            });
        }
    }

    public function down(): void
    {
        // The old values were placeholders; nothing to restore.
    }
};
