<?php

namespace App\Modules\Shop\Enums;

use App\Modules\Core\Traits\Enums\HasEnumHelpers;

enum PaymentStatusEnum: string
{
    use HasEnumHelpers;

    case UNPAID = 'unpaid';
    case PROCESSING = 'processing';
    case PAID = 'paid';

    public function label(): string
    {
        return match($this) {
            self::UNPAID     => 'Unpaid',
            self::PROCESSING => 'Processing',
            self::PAID       => 'Paid',
        };
    }
}
