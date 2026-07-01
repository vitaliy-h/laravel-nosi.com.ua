<?php

namespace App\Modules\Basket\Models;

use App\Modules\Shop\Models\Product;
use Database\Factories\BasketProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BasketProduct extends Model
{
    /** @use HasFactory<BasketProductFactory> */
    use HasFactory;

    protected $table = 'basket_shop_products';

    protected $fillable = [
        'basket_id',
        'shop_product_id',
        'product_name',
        'product_article',
        'selling_price',
        'quantity',
        'total',
    ];

    protected $casts = [
        'selling_price' => 'decimal:2',
        'quantity'   => 'integer',
        'total'      => 'decimal:2',
    ];

    public function basket()
    {
        return $this->belongsTo(Basket::class, 'basket_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'shop_product_id');
    }

    public function getTotalFormattedAttribute(): string
    {
        return t('shop.currency') . number_format($this->total, 2);
    }

    protected static function newFactory(): BasketProductFactory
    {
        return BasketProductFactory::new();
    }
}
