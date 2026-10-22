<?php

namespace App\Modules\ShopOrder\Enums;

use App\Modules\Core\Traits\Enums\HasEnumHelpers;

enum PaymentTypeEnum: string
{
    use HasEnumHelpers;

    case NONE = 'none';
    case ONLINE_PAYMENT = 'online_payment';
    case MANUAL_PAYMENT = 'manual_payment';

    public function label(): string
    {
        return __("enums.payment_type.{$this->value}");
    }
}
