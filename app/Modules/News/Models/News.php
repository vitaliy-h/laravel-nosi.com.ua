<?php

namespace App\Modules\News\Models;

use App\Modules\Core\Traits\Models\HasImage;
use App\Modules\Core\Traits\Models\HasVisibility;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasVisibility; // for visibility on the website
    use HasImage; // if there is an image

    protected $fillable = [
        'published_at', // main field for sorting
        'display', // related to HasVisibility
        'slug',
        'img', // main image
        'title',
        'meta_title',
        'meta_description',
        'short_text',
        'text',
    ];

    protected $casts = [
        'display' => 'boolean',
        'published_at' => 'datetime',
    ];
}
