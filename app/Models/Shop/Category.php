<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{

    protected $table = 'shop_categories';

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
