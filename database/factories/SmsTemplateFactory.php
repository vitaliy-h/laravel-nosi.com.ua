<?php

namespace Database\Factories;

use App\Modules\Notification\Enums\SmsEnum;
use App\Modules\Notification\Models\SmsTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SmsTemplate>
 */
class SmsTemplateFactory extends Factory
{
    protected $model = SmsTemplate::class;

    public function definition(): array
    {
        return [
            'key'     => fake()->unique()->randomElement(SmsEnum::cases()),
            'subject' => fake()->sentence(4),
            'body'    => fake()->paragraph(),
        ];
    }
}
