<?php

namespace Tests\Unit\Shop;

use App\Modules\Shop\Dtos\CreateOrderData;
use App\Modules\Shop\Dtos\OrderItemData;
use App\Modules\Shop\Enums\PaymentStatusEnum;
use App\Modules\Shop\Enums\PaymentTypeEnum;
use Tests\TestCase;

class OrderDtoTest extends TestCase
{
    public function test_order_item_data_maps_fields_and_types(): void
    {
        $item = OrderItemData::from([
            'shop_product_id' => 10,
            'product_name'    => 'Widget',
            'product_sku'     => 'SKU-1',
            'sale_price'      => '19.99',
            'quantity'        => '2',
        ]);

        $this->assertSame(10, $item->shop_product_id);
        $this->assertSame('Widget', $item->product_name);
        $this->assertSame('SKU-1', $item->product_sku);
        $this->assertSame(19.99, $item->sale_price);
        $this->assertSame(2.0, $item->quantity);
    }

    public function test_order_item_data_allows_null_sku(): void
    {
        $item = OrderItemData::from([
            'shop_product_id' => 1,
            'product_name'    => 'No SKU',
            'product_sku'     => null,
            'sale_price'      => 5,
            'quantity'        => 1,
        ]);

        $this->assertNull($item->product_sku);
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
                    'product_sku'     => 'SKU-1',
                    'sale_price'      => 19.99,
                    'quantity'        => 2,
                ],
            ],
        ]);

        $this->assertCount(1, $order->items);
        $this->assertInstanceOf(OrderItemData::class, $order->items[0]);
        $this->assertSame(10, $order->items[0]->shop_product_id);
    }
}
