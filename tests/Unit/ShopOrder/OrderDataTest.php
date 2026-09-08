<?php

namespace Tests\Unit\ShopOrder;

use App\Modules\ShopOrder\Dtos\CreateOrderData;
use App\Modules\ShopOrder\Dtos\OrderItemData;
use App\Modules\ShopOrder\Enums\PaymentStatusEnum;
use App\Modules\ShopOrder\Enums\PaymentTypeEnum;
use Tests\TestCase;

class OrderDataTest extends TestCase
{
    public function test_order_item_data_maps_fields_and_types(): void
    {
        $item = OrderItemData::from([
            'shop_product_id' => 10,
            'product_name'    => 'Widget',
            'product_article' => 'SKU-1',
            'selling_price'   => '19.99',
            'quantity'        => '2',
        ]);

        $this->assertSame(10, $item->shop_product_id);
        $this->assertSame('Widget', $item->product_name);
        $this->assertSame('SKU-1', $item->product_article);
        $this->assertSame(19.99, $item->selling_price);
        $this->assertSame(2, $item->quantity);
    }

    public function test_order_item_data_allows_null_sku(): void
    {
        $item = OrderItemData::from([
            'shop_product_id' => 1,
            'product_name'    => 'No SKU',
            'product_article' => null,
            'selling_price'   => 5,
            'quantity'        => 1,
        ]);

        $this->assertNull($item->product_article);
    }

    public function test_create_order_data_applies_defaults(): void
    {
        $order = CreateOrderData::from(['user_id' => 1]);

        $this->assertSame(1, $order->user_id);
        $this->assertSame(PaymentStatusEnum::UNPAID, $order->payment_status);
        $this->assertSame(PaymentTypeEnum::NONE, $order->payment_type);
        $this->assertSame([], $order->items);
    }

    public function test_create_order_data_hydrates_items(): void
    {
        $order = CreateOrderData::from([
            'user_id' => 1,
            'items'   => [
                [
                    'shop_product_id' => 10,
                    'product_name'    => 'Widget',
                    'product_article' => 'SKU-1',
                    'selling_price'   => 19.99,
                    'quantity'        => 2,
                ],
            ],
        ]);

        $this->assertCount(1, $order->items);
        $this->assertInstanceOf(OrderItemData::class, $order->items[0]);
        $this->assertSame(10, $order->items[0]->shop_product_id);
    }
}
