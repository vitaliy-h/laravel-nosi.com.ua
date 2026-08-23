<?php

namespace App\Modules\ShopOrders\Models;

use App\Modules\Core\Models\User;
use App\Modules\Core\Traits\Models\CascadesDeletes;
use App\Modules\Core\Traits\Models\HasUuid;
use App\Modules\ShopCart\Models\Promocode;
use App\Modules\ShopOrders\Enums\PaymentStatusEnum;
use App\Modules\ShopOrders\Enums\PaymentTypeEnum;
use App\Modules\ShopOrders\Services\OrderPermissions;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use CascadesDeletes;

    /** @use HasFactory<OrderFactory> */
    use HasFactory;
    use HasUuid;

    protected array $uuidFields = ['uuid'];

    protected array $cascadeDeletes = ['items'];

    protected $table = 'shop_orders';

    protected $fillable = [
        'uuid',
        'user_id',
        'total_amount',
        'subtotal_amount',
        'promocode_id',
        'promocode_code',
        'discount_amount',
        'payment_status',
        'payment_type',
        'payment_date',
        'user_name',
        'user_surname',
        'user_email',
        'user_phone',
        'comment',
    ];

    protected $guarded = [
        'order_number',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'subtotal_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'payment_status' => PaymentStatusEnum::class,
        'payment_type' => PaymentTypeEnum::class,
        'payment_date' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderProduct::class, 'shop_order_id');
    }

    public function promocode(): BelongsTo
    {
        return $this->belongsTo(Promocode::class);
    }

    public function getTotalAmountFormattedAttribute(): string
    {
        return t('shop.currency').number_format($this->total_amount, 2);
    }

    public function getSubtotalAmountFormattedAttribute(): string
    {
        return t('shop.currency').number_format($this->subtotal_amount, 2);
    }

    public function getDiscountAmountFormattedAttribute(): string
    {
        return t('shop.currency').number_format($this->discount_amount, 2);
    }

    public function getPermissionsAttribute(): OrderPermissions
    {
        return new OrderPermissions($this);
    }
}
