<?php

namespace App\Modules\Slider\Models;

use App\Modules\Core\Traits\Models\HasImage;
use App\Modules\Core\Traits\Models\HasVisibility;
use Illuminate\Database\Eloquent\Model;

/**
 * A slide of the home page hero.
 */
class Slider extends Model
{
    use HasImage;
    use HasVisibility;

    protected $fillable = [
        'display',
        'rank',
        'kicker',
        'title',
        'text',
        'button_text',
        'url',
        'img',
    ];

    protected $casts = [
        'display' => 'boolean',
    ];
}
