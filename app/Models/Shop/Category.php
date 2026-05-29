<?php

namespace App\Models\Shop;

use App\Modules\Core\Traits\Models\HasImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Kalnoy\Nestedset\NodeTrait;

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
