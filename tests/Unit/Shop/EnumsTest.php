<?php

namespace Tests\Unit\Shop;

use App\Modules\ShopOrders\Enums\PaymentStatusEnum;
use App\Modules\ShopOrders\Enums\PaymentTypeEnum;
use App\Modules\Shop\Enums\PerPageOptionEnum;
use Tests\TestCase;

class EnumsTest extends TestCase
{
    public function test_payment_status_labels(): void
    {
        $this->assertSame('Unpaid', PaymentStatusEnum::UNPAID->label());
        $this->assertSame('Processing', PaymentStatusEnum::PROCESSING->label());
        $this->assertSame('Paid', PaymentStatusEnum::PAID->label());
    }

    public function test_payment_type_labels(): void
    {
        $this->assertSame('None', PaymentTypeEnum::NONE->label());
        $this->assertSame('Online payment', PaymentTypeEnum::ONLINE_PAYMENT->label());
        $this->assertSame('Manual payment', PaymentTypeEnum::MANUAL_PAYMENT->label());
    }

    public function test_per_page_option_default_is_twelve(): void
    {
        $this->assertSame(PerPageOptionEnum::TWELVE, PerPageOptionEnum::default());
    }

    public function test_per_page_option_values_returns_all_case_values(): void
    {
        $this->assertSame([12, 24, 48, 96], PerPageOptionEnum::values());
    }
}
