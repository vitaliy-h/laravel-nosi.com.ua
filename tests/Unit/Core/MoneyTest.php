<?php

namespace Tests\Unit\Core;

use App\Modules\Core\Support\Money;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MoneyTest extends TestCase
{
    public function test_an_amount_is_rounded_to_the_two_decimals_the_schema_stores(): void
    {
        $this->assertSame(10.01, Money::from(10.005)->value());
        $this->assertSame(10.0, Money::from(9.999)->value());
    }

    /**
     * `decimal:2` hands back a string and the database sums come back as
     * strings too, so both have to be accepted.
     */
    #[DataProvider('amounts')]
    public function test_an_amount_can_arrive_as_a_string_a_number_or_nothing(mixed $input, float $expected): void
    {
        $this->assertSame($expected, Money::from($input)->value());
    }

    /**
     * @return array<string, array{mixed, float}>
     */
    public static function amounts(): array
    {
        return [
            'decimal string' => ['35.00', 35.0],
            'float'          => [35.0, 35.0],
            'integer'        => [35, 35.0],
            'null'           => [null, 0.0],
        ];
    }

    public function test_formatting_puts_the_currency_in_front_of_two_decimals(): void
    {
        $this->assertSame(t('shop.currency') . '1,234.50', Money::from(1234.5)->format());
    }

    public function test_subtraction_rounds_the_result(): void
    {
        $this->assertSame(0.1, Money::from(0.3)->minus(Money::from(0.2))->value());
    }

    public function test_at_least_is_the_floor_and_at_most_is_the_ceiling(): void
    {
        $this->assertSame(0.0, Money::from(-5)->atLeast(Money::zero())->value());
        $this->assertSame(-5.0, Money::from(-5)->atMost(Money::zero())->value());
        $this->assertSame(20.0, Money::from(50)->atMost(Money::from(20))->value());
        $this->assertSame(50.0, Money::from(50)->atLeast(Money::from(20))->value());
    }

    public function test_only_an_amount_above_zero_is_positive(): void
    {
        $this->assertTrue(Money::from(0.01)->isPositive());
        $this->assertFalse(Money::zero()->isPositive());
        $this->assertFalse(Money::from(-1)->isPositive());
    }
}
