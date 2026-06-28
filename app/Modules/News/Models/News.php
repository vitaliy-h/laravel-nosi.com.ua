<?php

namespace App\Modules\News\Models;

use App\Modules\Core\Traits\Models\HasImage;
use App\Modules\Core\Traits\Models\HasSeoFields;
use App\Modules\Core\Traits\Models\HasVisibility;
use Database\Factories\NewsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    /** @use HasFactory<NewsFactory> */
    use HasFactory;
    use HasVisibility; // for visibility on the website
    use HasImage; // if there is an image
    use HasSeoFields;

    protected array $imgArrayFields = ['imgs'];

    protected $fillable = [
        'published_at', // main field for sorting
        'display', // related to HasVisibility
        'slug',
        'img', // main image
        'title',
        'meta_title',
        'meta_description',
        'short_text',
        'content',
    ];

    protected $casts = [
        'display' => 'boolean',
        'published_at' => 'datetime',
        'content'=>'array'
    ];
}
