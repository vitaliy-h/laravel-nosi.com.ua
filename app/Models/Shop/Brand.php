<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;

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
}
