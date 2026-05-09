<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model
{

    protected $table = 'shop_brands';

    protected $fillable = [
        'display',
        'rank',
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
