<?php

namespace App\Modules\ShopCart\Models;

use App\Modules\Shop\Models\Product;
use Database\Factories\CartProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartProduct extends Model
{
    /** @use HasFactory<CartProductFactory> */
    use HasFactory;

    protected $table = 'shop_cart_products';

    protected $fillable = [
        'cart_id',
        'shop_product_id',
        'product_name',
        'product_article',
        'selling_price',
        'quantity',
        'total',
    ];

    protected $casts = [
        'selling_price' => 'decimal:2',
        'quantity'      => 'integer',
        'total'         => 'decimal:2',
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class, 'cart_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'shop_product_id');
    }

    protected static function newFactory(): CartProductFactory
    {
        return CartProductFactory::new();
    }
}
