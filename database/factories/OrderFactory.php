<?php

namespace Database\Factories;

use App\Modules\Core\Models\User;
use App\Modules\Shop\Enums\PaymentStatusEnum;
use App\Modules\Shop\Enums\PaymentTypeEnum;
use App\Modules\Shop\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * @var class-string<Order>
     */
    protected $model = Order::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'total_amount' => 0,
            'payment_status' => PaymentStatusEnum::UNPAID,
            'payment_type' => PaymentTypeEnum::NONE,
        ];
    }
}
