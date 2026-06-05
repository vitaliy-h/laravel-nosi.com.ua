<?php

namespace App\Modules\Shop\Models;

use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{
    protected $table = 'shop_order_shop_products';

    protected $fillable = [
        'shop_order_id',
        'shop_product_id',
        'product_name',
        'product_sku',
        'sale_price',
        'quantity',
        'total',
    ];

    protected $casts = [
        'sale_price' => 'decimal:2',
        'quantity'   => 'decimal:2',
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
}
