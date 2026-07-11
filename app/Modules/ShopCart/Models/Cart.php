<?php

namespace App\Modules\ShopCart\Models;

use App\Modules\Core\Models\User;
use App\Modules\Core\Traits\Models\CascadesDeletes;
use App\Modules\Core\Traits\Models\HasUuid;
use Database\Factories\CartFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    /** @use HasFactory<CartFactory> */
    use HasFactory;
    use CascadesDeletes;
    use HasUuid;

    protected array $uuidFields = ['uuid'];

    protected array $cascadeDeletes = ['items'];

    protected $table = 'shop_carts';

    protected $fillable = [
        'uuid',
        'user_id',
        'total_amount',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CartProduct::class, 'cart_id');
    }

    public function getTotalAmountFormattedAttribute(): string
    {
        return t('shop.currency') . number_format($this->total_amount, 2);
    }

    protected static function newFactory(): CartFactory
    {
        return CartFactory::new();
    }
}
