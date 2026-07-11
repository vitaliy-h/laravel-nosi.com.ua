<?php

namespace App\Modules\ShopCart\Enums;

use App\Modules\Core\Traits\Enums\HasEnumHelpers;

enum CartAction: string
{
    use HasEnumHelpers;

    case DeleteModal = 'delete_modal';
    case DeleteCheckout = 'delete_checkout';
    case ChangeCount = 'change_count';
    case ChangeCountModal = 'change_count_modal';
    case ChangeCountCheckout = 'change_count_checkout';
}