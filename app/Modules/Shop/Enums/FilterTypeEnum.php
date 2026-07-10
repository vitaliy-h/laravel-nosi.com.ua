<?php

namespace App\Modules\Shop\Enums;

use App\Modules\Core\Traits\Enums\HasEnumHelpers;

enum FilterTypeEnum: string
{
    use HasEnumHelpers;

    case IN = 'in';
    case RANGE = 'range';
    case SEARCH = 'search';
}
