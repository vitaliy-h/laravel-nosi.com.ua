<?php

namespace App\Modules\ShopOrder\Models;

use App\Modules\Shop\Models\Product;
use Database\Factories\OrderProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{
    /** @use HasFactory<OrderProductFactory> */
    use HasFactory;

    protected $table = 'shop_order_products';

    protected $fillable = [
        'shop_order_id',
        'shop_product_id',
        'pack_quantity',
        'product_name',
        'product_article',
        'selling_price',
        'quantity',
        'total',
    ];

    protected $casts = [
        'selling_price' => 'decimal:2',
        'pack_quantity' => 'integer',
        'quantity'      => 'integer',
        'total'         => 'decimal:2',
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
