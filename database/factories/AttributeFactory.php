<?php

namespace Database\Factories;

use App\Modules\Shop\Models\Attribute;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attribute>
 */
class AttributeFactory extends Factory
{
    /**
     * @var class-string<Attribute>
     */
    protected $model = Attribute::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'display'       => true,
            'is_filterable' => true,
            'title'         => fake()->word(),
            'code'          => fake()->unique()->lexify('attr_????'),
        ];
    }

    /**
     * With values, titled as given, in this order.
     *
     * @param  array<int, string>  $titles
     */
    public function withOptions(array $titles): static
    {
        return $this->afterCreating(function (Attribute $attribute) use ($titles): void {
            foreach (array_values($titles) as $i => $title) {
                $attribute->options()->create(['title' => $title, 'rank' => $i + 1]);
            }
        });
    }
}
