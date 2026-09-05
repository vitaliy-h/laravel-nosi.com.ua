<?php

namespace Database\Factories;

use App\Modules\Notification\Enums\LetterEnum;
use App\Modules\Notification\Enums\LogLetterStatusEnum;
use App\Modules\Notification\Enums\NotificationSourceEnum;
use App\Modules\Notification\Models\LogLetter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LogLetter>
 */
class LogLetterFactory extends Factory
{
    protected $model = LogLetter::class;

    public function definition(): array
    {
        return [
            'template_key'  => fake()->randomElement(LetterEnum::cases()),
            'receiver'      => fake()->safeEmail(),
            'subject'       => fake()->sentence(4),
            'sender'        => fake()->safeEmail(),
            'sender_name'   => fake()->company(),
            'body'          => fake()->paragraph(),
            'content'       => fake()->paragraph(),
            'status'        => LogLetterStatusEnum::SENT,
            'source'        => NotificationSourceEnum::SYSTEM,
            'error_message' => null,
            'attachments'   => null,
        ];
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'        => LogLetterStatusEnum::FAILED,
            'error_message' => fake()->sentence(),
        ]);
    }
}
