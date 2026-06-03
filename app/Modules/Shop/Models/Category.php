<?php

namespace App\Modules\Shop\Models;

use App\Modules\Core\Traits\Models\HasImage;
use App\Modules\Core\Traits\Models\HasVisibility;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
    /*
        protected $appends = [
            '_active',
            '_opened',
        ];

        protected $attributes = [
            '_active' => 0,
            '_opened' => 0,
        ];
    /**/
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function childrenRecursive(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->visible()
            ->orderBy('_lft')
            ->with('childrenRecursive');
    }

    public function getActiveAttribute(): int
    {
        return $this->attributes['_active'] ?? 0;
    }

    public function getOpenedAttribute(): int
    {
        return $this->attributes['_opened'] ?? 0;
    }
}
