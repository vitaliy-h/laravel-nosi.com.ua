<?php

namespace Database\Factories;

use App\Modules\Geo\Models\GeoCountry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GeoCountry>
 */
class GeoCountryFactory extends Factory
{
    protected $model = GeoCountry::class;

    public function definition(): array
    {
        return [
            'title'         => fake()->unique()->country(),
            'iso2'          => fake()->unique()->countryCode(),
            'iso3'          => strtoupper(fake()->lexify('???')),
            'phone_code'    => '+' . fake()->numberBetween(1, 999),
            'currency_code' => strtoupper(fake()->currencyCode()),
            'timezone'      => fake()->timezone(),
            'rank'          => fake()->numberBetween(0, 10),
            'display'       => true,
        ];
    }
}
