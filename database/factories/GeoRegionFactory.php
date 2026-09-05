<?php

namespace Database\Factories;

use App\Modules\Geo\Models\GeoCountry;
use App\Modules\Geo\Models\GeoRegion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GeoRegion>
 */
class GeoRegionFactory extends Factory
{
    protected $model = GeoRegion::class;

    public function definition(): array
    {
        return [
            'geo_country_id' => GeoCountry::factory(),
            'title'          => fake()->state(),
            'code'           => strtoupper(fake()->unique()->lexify('??')),
            'rank'           => fake()->numberBetween(0, 10),
            'display'        => true,
        ];
    }

    public function forCountry(GeoCountry $country): static
    {
        return $this->state(fn (array $attributes) => [
            'geo_country_id' => $country->id,
        ]);
    }
}
