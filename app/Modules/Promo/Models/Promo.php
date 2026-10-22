<?php

namespace App\Modules\Promo\Models;

use App\Modules\Core\Traits\Models\HasImage;
use App\Modules\Core\Traits\Models\HasVisibility;
use App\Modules\Promo\Enums\PromoStyleEnum;
use Illuminate\Database\Eloquent\Model;

/**
 * A banner of the home page promo grid. The grid lays itself out by how many
 * banners are visible: one — full width, two — 1.35 / 1, three — small / big / small.
 */
class Promo extends Model
{
    use HasImage;
    use HasVisibility;

    protected $fillable = [
        'display',
        'rank',
        'style',
        'kicker',
        'title',
        'big_text',
        'text',
        'button_text',
        'url',
        'img',
    ];

    protected $casts = [
        'display' => 'boolean',
        'style'   => PromoStyleEnum::class,
    ];
}
