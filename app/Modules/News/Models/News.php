<?php

namespace App\Modules\News\Models;

use App\Modules\Core\Traits\Models\HasImage;
use App\Modules\Core\Traits\Models\HasVisibility;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasVisibility;
    use HasImage;

    protected $fillable = [
        'title',
        'slug',
        'short_text',
        'text',
        'img',
        'display',
        'published_at',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'display' => 'boolean',
        'published_at' => 'datetime',
    ];
}
