<?php

namespace Database\Factories;

use App\Modules\Notification\Enums\LetterEnum;
use App\Modules\Notification\Models\LetterTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LetterTemplate>
 */
class LetterTemplateFactory extends Factory
{
    protected $model = LetterTemplate::class;

    public function definition(): array
    {
        return [
            'key'         => fake()->unique()->randomElement(LetterEnum::cases()),
            'sender'      => fake()->safeEmail(),
            'sender_name' => fake()->company(),
            'receivers'   => fake()->safeEmail(),
            'subject'     => fake()->sentence(5),
            'body'        => fake()->paragraph(),
        ];
    }
}
