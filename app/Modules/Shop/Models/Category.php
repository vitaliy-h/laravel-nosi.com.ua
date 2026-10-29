<?php

namespace App\Modules\Shop\Models;

use App\Modules\Core\Traits\Models\HasImage;
use App\Modules\Core\Traits\Models\HasSeoFields;
use App\Modules\Core\Traits\Models\HasVisibility;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Kalnoy\Nestedset\NodeTrait;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    use HasImage;
    use HasSeoFields;
    use HasVisibility;
    use NodeTrait;

    protected $table = 'shop_categories';

    protected $fillable = [
        'parent_id',
        '_lft',
        '_rgt',
        'rank',
        'display',
        'display_menu',
        'display_menu_footer',
        'title',
        'slug',
        'img',
        'text',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'display'             => 'boolean',
        'display_menu'        => 'boolean',
        'display_menu_footer' => 'boolean',
    ];

    /** «Жіночі шкарпетки — купити в інтернет-магазині Nosi» */
    public function seoMetaTitle(): string
    {
        return $this->getSeoTitle() . ' — купити в інтернет-магазині ' . $this->seoShopName();
    }

    /** «Купити жіночі шкарпетки в інтернет-магазині Nosi: оригінальні бренди, доставка…» */
    public function seoMetaDescription(): string
    {
        return 'Купити ' . $this->seoLowerFirst($this->getSeoTitle()) . ' в інтернет-магазині ' . $this->seoShopName()
            . ': оригінальні бренди, доставка Новою поштою по всій Україні, обмін і повернення 14 днів.';
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
