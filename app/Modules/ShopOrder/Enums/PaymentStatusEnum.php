<?php

namespace App\Modules\ShopOrder\Enums;

use App\Modules\Core\Traits\Enums\HasEnumHelpers;

enum PaymentStatusEnum: string
{
    use HasEnumHelpers;

    case UNPAID = 'unpaid';
    case PROCESSING = 'processing';
    case PAID = 'paid';

    public function label(): string
    {
        return __("enums.payment_status.{$this->value}");
    }
}
