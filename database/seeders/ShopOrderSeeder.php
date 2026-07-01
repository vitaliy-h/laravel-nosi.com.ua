<?php

namespace Database\Seeders;

use App\Modules\Core\Models\User;
use App\Modules\Shop\Actions\CreateOrderAction;
use App\Modules\Shop\Actions\GenerateOrderNumberAction;
use App\Modules\Shop\Dtos\CreateOrderData;
use App\Modules\Shop\Dtos\OrderItemData;
use App\Modules\Shop\Enums\PaymentStatusEnum;
use App\Modules\Shop\Enums\PaymentTypeEnum;
use App\Modules\Shop\Models\Order;
use App\Modules\Shop\Models\OrderProduct;
use App\Modules\Shop\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShopOrderSeeder extends Seeder
{
    public function __construct(private readonly CreateOrderAction $createOrder, private readonly GenerateOrderNumberAction $generateOrderNumberAction)
    {
    }

    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        OrderProduct::truncate();
        Order::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $user = User::where('email', 'admin')->firstOrFail();
        $products = Product::inRandomOrder()->take(6)->get();

        if ($products->isEmpty()) {
            $this->command->warn('No products found. Run ShopProductSeeder first.');
            return;
        }

        $orders = [
            [
                'payment_status' => PaymentStatusEnum::UNPAID,
                'payment_type' => PaymentTypeEnum::NONE,
                'item_count' => 2,
            ],
            [
                'payment_status' => PaymentStatusEnum::PROCESSING,
                'payment_type' => PaymentTypeEnum::ONLINE_PAYMENT,
                'item_count' => 3,
            ],
            [
                'payment_status' => PaymentStatusEnum::PAID,
                'payment_type' => PaymentTypeEnum::MANUAL_PAYMENT,
                'item_count' => 1,
            ],
            [
                'payment_status' => PaymentStatusEnum::PAID,
                'payment_type' => PaymentTypeEnum::ONLINE_PAYMENT,
                'item_count' => 1,
            ],
            [
                'payment_status' => PaymentStatusEnum::PROCESSING,
                'payment_type' => PaymentTypeEnum::NONE,
                'item_count' => 5,
            ],
            [
                'payment_status' => PaymentStatusEnum::PAID,
                'payment_type' => PaymentTypeEnum::MANUAL_PAYMENT,
                'item_count' => 6,
            ],
            [
                'payment_status' => PaymentStatusEnum::PROCESSING,
                'payment_type' => PaymentTypeEnum::NONE,
                'item_count' => 3,
            ],
            [
                'payment_status' => PaymentStatusEnum::PAID,
                'payment_type' => PaymentTypeEnum::ONLINE_PAYMENT,
                'item_count' => 8,
            ],
            [
                'payment_status' => PaymentStatusEnum::PROCESSING,
                'payment_type' => PaymentTypeEnum::MANUAL_PAYMENT,
                'item_count' => 1,
            ],
        ];

        $productChunks = $products->chunk(10);

        foreach ($orders as $index => $config) {
            $chunk = $productChunks->get($index % $productChunks->count(), $productChunks->first());

            $items = $chunk->take($config['item_count'])->map(fn(Product $product) => new OrderItemData(
                shop_product_id: $product->id,
                product_name: $product->title,
                product_article: $product->article ?? null,
                selling_price: $product->price,
                quantity: fake()->randomElement([1, 2, 3]),
            ))->values()->all();

            $orderDb = $this->createOrder->handle(new CreateOrderData(
                user_id: $user->id,
                payment_status: $config['payment_status'],
                payment_type: $config['payment_type'],
                items: $items,
            ));
            $this->generateOrderNumberAction->handle($orderDb);
        }
    }
}
