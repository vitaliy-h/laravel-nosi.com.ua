<?php

namespace App\Modules\ShopOrders\Enums;

use App\Modules\Core\Traits\Enums\HasEnumHelpers;

enum PaymentTypeEnum: string
{
    use HasEnumHelpers;

    case NONE = 'none';
    case ONLINE_PAYMENT = 'online_payment';
    case MANUAL_PAYMENT = 'manual_payment';

    public function label(): string
    {
        return match($this) {
            self::NONE           => 'None',
            self::ONLINE_PAYMENT => 'Online payment',
            self::MANUAL_PAYMENT => 'Manual payment',
        };
    }
}
