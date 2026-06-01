<?php

namespace App\Modules\Shop\Models;

use App\Modules\Core\Traits\Models\HasImage;
use App\Modules\Core\Traits\Models\HasVisibility;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Kalnoy\Nestedset\NodeTrait;

class Category extends Model
{
    use HasVisibility;
    use HasImage;
    use NodeTrait;

    protected $table = 'shop_categories';

    protected $fillable = [
        'parent_id',
        '_lft',
        '_rgt',
        'rank',
        'display',
        'title',
        'slug',
        'img',
        'text',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'display' => 'boolean',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
