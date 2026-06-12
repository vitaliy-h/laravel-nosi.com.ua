<?php

namespace Tests\Unit\Shop;

use App\Modules\Shop\Enums\PaymentStatusEnum;
use App\Modules\Shop\Enums\PaymentTypeEnum;
use Tests\TestCase;

class PaymentEnumsTest extends TestCase
{
    public function test_payment_status_options_have_expected_keys_and_labels(): void
    {
        $options = PaymentStatusEnum::options();

        $this->assertSame([
            'unpaid'     => 'Unpaid',
            'processing' => 'Processing',
            'paid'       => 'Paid',
        ], $options);
    }

    public function test_payment_status_label(): void
    {
        $this->assertSame('Unpaid', PaymentStatusEnum::UNPAID->label());
    }

    public function test_payment_type_options_contain_all_cases(): void
    {
        $options = PaymentTypeEnum::options();

        $this->assertNotEmpty($options);
        $this->assertCount(count(PaymentTypeEnum::cases()), $options);

        foreach (PaymentTypeEnum::cases() as $case) {
            $this->assertArrayHasKey($case->value, $options);
            $this->assertSame($case->label(), $options[$case->value]);
        }
    }
}
