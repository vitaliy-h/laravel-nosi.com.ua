<?php

namespace App\Modules\Shop\Models;

use App\Modules\Core\Traits\Models\HasImage;
use App\Modules\Core\Traits\Models\HasSeoFields;
use App\Modules\Core\Traits\Models\HasVisibility;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasVisibility;
    use HasImage;
    use HasSeoFields;

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

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function getSlugWithIdAttribute()
    {
       return $this->slug.'-'.$this->id;
    }
}
