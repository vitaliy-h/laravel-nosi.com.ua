<?php

namespace App\Modules\ShopCart\Models;

use App\Modules\ShopCart\Enums\PromocodeTypeEnum;
use Database\Factories\PromocodeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Promocode extends Model
{
    /** @use HasFactory<PromocodeFactory> */
    use HasFactory;

    protected $table = 'shop_promocodes';

    protected $fillable = [
        'code',
        'type',
        'value',
        'min_order_amount',
        'max_discount',
        'starts_at',
        'expires_at',
        'usage_limit',
        'used_count',
        'display',
        'comment',
    ];

    protected $casts = [
        'type' => PromocodeTypeEnum::class,
        'value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'usage_limit' => 'integer',
        'used_count' => 'integer',
        'display' => 'boolean',
    ];

    /**
     * Codes are compared case-insensitively — store them upper-cased.
     */
    public function setCodeAttribute(?string $value): void
    {
        $this->attributes['code'] = $value === null ? null : Str::upper(trim($value));
    }

    /**
     * Enabled, inside its date window and not exhausted.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('display', true)
            ->where(fn (Builder $q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()))
            ->where(fn (Builder $q) => $q->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit'));
    }

    public function hasReachedLimit(): bool
    {
        return $this->usage_limit !== null && $this->used_count >= $this->usage_limit;
    }

    public function getDiscountLabelAttribute(): string
    {
        return $this->type === PromocodeTypeEnum::PERCENT
            ? rtrim(rtrim(number_format((float) $this->value, 2, '.', ''), '0'), '.').'%'
            : t('shop.currency').number_format((float) $this->value, 2);
    }

    protected static function newFactory(): PromocodeFactory
    {
        return PromocodeFactory::new();
    }
}
