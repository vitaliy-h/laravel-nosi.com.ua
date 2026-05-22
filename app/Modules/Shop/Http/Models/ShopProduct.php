<?php

namespace App\Modules\Shop\Http\Models;

use App\Modules\Core\Traits\HasImage;
use App\Modules\Core\Traits\HasVisibility;
use Illuminate\Database\Eloquent\Model;

class ShopProduct extends Model
{
    use HasVisibility;
    use HasImage;

    protected $table = 'shop_products';

    protected $fillable = [
        'display',
        'category_id',
        'brand_id',
        'price',
        'price2',
        'article',
        'title',
        'slug',
        'img',
        'text',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'display' => 'boolean',
        'price' => 'float',
        'price2' => 'float',
    ];
}
