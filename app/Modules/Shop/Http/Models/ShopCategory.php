<?php

namespace App\Modules\Shop\Http\Models;

use App\Modules\Core\Traits\HasImage;
use App\Modules\Core\Traits\HasVisibility;
use Illuminate\Database\Eloquent\Model;

class ShopCategory extends Model
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
