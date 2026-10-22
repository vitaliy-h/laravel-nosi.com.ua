<?php

namespace App\Modules\Promo\Enums;

use App\Modules\Core\Traits\Enums\HasEnumHelpers;

/**
 * Background of a promo banner. With an image uploaded the picture wins and
 * the colour only shows while it loads.
 */
enum PromoStyleEnum: string
{
    use HasEnumHelpers;

    case PLUM = 'plum';
    case ACCENT = 'accent';
    case LIME = 'lime';

    public function label(): string
    {
        return __("enums.promo_style.{$this->value}");
    }
}
