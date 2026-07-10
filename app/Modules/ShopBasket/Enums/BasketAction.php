<?php

namespace App\Modules\ShopBasket\Enums;

use App\Modules\Core\Traits\Enums\HasEnumHelpers;

enum BasketAction: string
{
    use HasEnumHelpers;

    case DeleteModal = 'delete_modal';
    case DeleteCheckout = 'delete_checkout';
    case ChangeCount = 'change_count';
    case ChangeCountModal = 'change_count_modal';
    case ChangeCountCheckout = 'change_count_checkout';
}