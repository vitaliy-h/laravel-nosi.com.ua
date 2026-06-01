<?php

namespace App\Modules\Shop\Models;

use App\Modules\Core\Traits\Models\HasImage;
use App\Modules\Core\Traits\Models\HasVisibility;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasVisibility;
    use HasImage;

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
}
