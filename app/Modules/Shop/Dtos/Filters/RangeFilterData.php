<?php

namespace App\Modules\Shop\Dtos\Filters;

use App\Modules\Shop\Enums\FilterTypeEnum;
use Illuminate\Database\Eloquent\Builder;

class RangeFilterData extends BaseFilterData
{
    public function __construct(
        public readonly string $key,
        public readonly FilterTypeEnum $type,
        public readonly float $min,
        public readonly float $max,
        public readonly ?float $from,
        public readonly ?float $to,
    ) {}

    public function withRange(float $min, float $max): self
    {
        return new self($this->key, $this->type, $min, $max, $this->from, $this->to);
    }

    public function fromValue(): float
    {
        return $this->from ?? $this->min;
    }

    public function toValue(): float
    {
        return $this->to ?? $this->max;
    }

    /**
     * Clamp the current "from" value into the [min, max] range,
     * resetting to the full range when "from" would end up above "to".
     */
    public function fromSlider(): float
    {
        return $this->clampedFrom() > $this->clampedTo() ? $this->min : $this->clampedFrom();
    }

    /**
     * Clamp the current "to" value into the [min, max] range,
     * resetting to the full range when "to" would end up below "from".
     */
    public function toSlider(): float
    {
        return $this->clampedFrom() > $this->clampedTo() ? $this->max : $this->clampedTo();
    }

    private function clampedFrom(): float
    {
        return max($this->min, min($this->fromValue(), $this->max));
    }

    private function clampedTo(): float
    {
        return max($this->min, min($this->toValue(), $this->max));
    }

    public function apply(Builder $query): void
    {
        if ($this->from !== null) {
            $query->where($this->key, '>=', $this->from);
        }

        if ($this->to !== null) {
            $query->where($this->key, '<=', $this->to);
        }
    }
}
