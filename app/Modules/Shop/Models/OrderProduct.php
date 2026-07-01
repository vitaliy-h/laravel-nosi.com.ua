<?php

namespace App\Modules\Shop\Models;

use Database\Factories\OrderProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{
    /** @use HasFactory<OrderProductFactory> */
    use HasFactory;

    protected $table = 'shop_order_shop_products';

    protected $fillable = [
        'shop_order_id',
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

    public function order()
    {
        return $this->belongsTo(Order::class, 'shop_order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'shop_product_id');
    }

    public function getTotalFormattedAttribute(): string
    {
        return t('shop.currency') . number_format($this->total, 2);
    }
}
