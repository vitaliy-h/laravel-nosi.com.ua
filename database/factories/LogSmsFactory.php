<?php

namespace Database\Factories;

use App\Modules\Notification\Enums\LogSmsStatusEnum;
use App\Modules\Notification\Enums\NotificationSourceEnum;
use App\Modules\Notification\Enums\SmsEnum;
use App\Modules\Notification\Models\LogSms;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LogSms>
 */
class LogSmsFactory extends Factory
{
    protected $model = LogSms::class;

    public function definition(): array
    {
        return [
            'template_key'  => fake()->randomElement(SmsEnum::cases()),
            'receiver'      => fake()->phoneNumber(),
            'body'          => fake()->sentence(),
            'status'        => LogSmsStatusEnum::SENT,
            'source'        => NotificationSourceEnum::SYSTEM,
            'error_message' => null,
        ];
    }

    public function failed(): static
    {
        return $this->state(fn(array $attributes) => [
            'status'        => LogSmsStatusEnum::FAILED,
            'error_message' => fake()->sentence(),
        ]);
    }
}
