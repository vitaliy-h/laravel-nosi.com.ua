<?php

namespace App\Modules\Shop\Models;

use App\Modules\Core\Models\User;
use App\Modules\Shop\Enums\PaymentStatusEnum;
use App\Modules\Shop\Enums\PaymentTypeEnum;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'shop_orders';

    protected $fillable = [
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
}
