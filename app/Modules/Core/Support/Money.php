<?php

namespace App\Modules\Core\Support;

/**
 * An amount of money, rounded to the two decimals the schema stores.
 *
 * Two jobs, both of which used to be scattered: it is the one place that turns
 * an amount into the string a customer reads (models must not format — see
 * `.claude/backend.md`, «Presentation»), and the one place that adds and
 * subtracts them, so rounding happens at every step instead of once at the end.
 *
 * It is deliberately **not** an Eloquent cast: `shop_*.total_amount` and friends
 * stay `decimal:2`, so Filament, the dashboard queries and the API keep reading
 * plain numbers. Money is built where an amount is used — `Money::from($order->
 * total_amount)->format()` in a ViewModel, `Money::from(...)->minus(...)` in an
 * Action.
 */
final class Money
{
    private function __construct(
        private readonly float $amount,
    ) {}

    public static function from(float|int|string|null $amount): self
    {
        return new self(round((float) $amount, 2));
    }

    public static function zero(): self
    {
        return new self(0.0);
    }

    public function value(): float
    {
        return $this->amount;
    }

    /**
     * The amount as a customer reads it: thousands split by a space, the symbol
     * after the number — «1 399 ₴». Kopecks are shown only when there are any.
     */
    public function format(): string
    {
        $decimals = fmod($this->amount, 1.0) == 0.0 ? 0 : 2;

        return number_format($this->amount, $decimals, ',', ' ') . ' ' . t('shop.currency');
    }

    public function minus(self $other): self
    {
        return self::from($this->amount - $other->amount);
    }

    /**
     * Not below $floor — `Money::from($subtotal - $discount)->atLeast(Money::zero())`
     * is the "a total can never go negative" rule, spelled out.
     */
    public function atLeast(self $floor): self
    {
        return $this->amount < $floor->amount ? $floor : $this;
    }

    /**
     * Not above $ceiling: a discount cannot exceed what is being discounted.
     */
    public function atMost(self $ceiling): self
    {
        return $this->amount > $ceiling->amount ? $ceiling : $this;
    }

    public function isPositive(): bool
    {
        return $this->amount > 0;
    }
}
