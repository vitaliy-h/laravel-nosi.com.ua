<?php

namespace App\Models\Shop;

use App\Modules\Core\Traits\Models\HasImage;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
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

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
}
