<?php

namespace App\Modules\Shop\Models;

use App\Modules\Core\Models\User;
use App\Modules\Core\Traits\Models\CascadesDeletes;
use App\Modules\Core\Traits\Models\HasUuid;
use App\Modules\Shop\Enums\PaymentStatusEnum;
use App\Modules\Shop\Enums\PaymentTypeEnum;
use App\Modules\Shop\Services\OrderActions;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;
    use CascadesDeletes;
    use HasUuid;

    protected array $uuidFields = ['uuid'];

    protected array $cascadeDeletes = ['items'];

    protected $table = 'shop_orders';

    protected $fillable = [
        'uuid',
        'user_id',
        'total_amount',
        'payment_status',
        'payment_type',
        'payment_date',
    ];

    protected $guarded = [
        'order_number',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'payment_status' => PaymentStatusEnum::class,
        'payment_type' => PaymentTypeEnum::class,
        'payment_date' => 'datetime',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderProduct::class, 'shop_order_id');
    }

    public function getTotalAmountFormattedAttribute(): string
    {
        return t('shop.currency') . number_format($this->total_amount, 2);
    }

    public function getActionsAttribute(): OrderActions
    {
        return new OrderActions($this);
    }
}
