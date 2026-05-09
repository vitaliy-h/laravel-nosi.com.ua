<?php

namespace App\Models\Shop;

use App\Modules\Core\Traits\HasImage;
use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use NodeTrait;
    use HasImage;

    protected $table = 'shop_categories';

    protected $fillable = [
        'display',
        'rank',
        'parent_id',
        'title',
        'slug',
        'img',
        'text',
        'meta_title',
        'meta_description',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
