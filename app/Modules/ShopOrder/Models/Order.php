<?php

namespace App\Modules\ShopOrder\Models;

use App\Modules\Core\Traits\Models\CascadesDeletes;
use App\Modules\Core\Traits\Models\HasUuid;
use App\Modules\ShopCart\Models\Promocode;
use App\Modules\ShopOrder\Enums\PaymentStatusEnum;
use App\Modules\ShopOrder\Enums\PaymentTypeEnum;
use App\Modules\ShopOrder\Services\OrderPermissions;
use App\Modules\User\Models\User;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
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

    /**
     * `order_number` is deliberately absent: it is assigned from the order's own
     * id by GenerateOrderNumberAction once the row exists, and must never arrive
     * from a request. A non-empty $fillable is the whole guard — Laravel never
     * looks at $guarded when this list is set, so listing the column there as
     * well only looked like protection.
     */
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

    protected $casts = [
        'total_amount'    => 'decimal:2',
        'subtotal_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'payment_status'  => PaymentStatusEnum::class,
        'payment_type'    => PaymentTypeEnum::class,
        'payment_date'    => 'datetime',
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

    /**
     * Cached: blade asks an order what it may do inside the loop that renders
     * it, and every ask used to build another OrderPermissions. The object reads
     * the model live, so caching it changes nothing about the answers.
     */
    protected function permissions(): Attribute
    {
        return Attribute::make(
            get: fn (): OrderPermissions => new OrderPermissions($this),
        )->shouldCache();
    }
}
