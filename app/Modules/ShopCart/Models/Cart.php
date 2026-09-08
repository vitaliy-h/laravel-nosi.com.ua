<?php

namespace App\Modules\ShopCart\Models;

use App\Modules\Core\Traits\Models\CascadesDeletes;
use App\Modules\Core\Traits\Models\HasUuid;
use App\Modules\User\Models\User;
use Database\Factories\CartFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    use CascadesDeletes;

    /** @use HasFactory<CartFactory> */
    use HasFactory;

    use HasUuid;

    protected array $uuidFields = ['uuid'];

    protected array $cascadeDeletes = ['items'];

    protected $table = 'shop_carts';

    protected $fillable = [
        'uuid',
        'user_id',
        'total_amount',
        'subtotal_amount',
        'promocode_id',
        'promocode_code',
        'discount_amount',
    ];

    protected $casts = [
        'total_amount'    => 'decimal:2',
        'subtotal_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartProduct::class, 'cart_id');
    }

    public function promocode(): BelongsTo
    {
        return $this->belongsTo(Promocode::class);
    }

    protected static function newFactory(): CartFactory
    {
        return CartFactory::new();
    }
}
