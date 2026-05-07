<?php

namespace App\Modules\News\Models;

use App\Modules\Core\Traits\HasImage;
use App\Modules\Core\Traits\HasSlug;
use App\Modules\Core\Traits\HasVisibility;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasVisibility;
    use HasSlug;
    use HasImage;

    protected $fillable = [
        'title',
        'slug',
        'short_text',
        'text',
        'img',
        'display',
        'published_at',
    ];

    protected $casts = [
        'display' => 'boolean',
        'published_at' => 'datetime',
    ];
}
