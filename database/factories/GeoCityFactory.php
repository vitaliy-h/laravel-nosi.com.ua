<?php

namespace Database\Factories;

use App\Modules\Geo\Models\GeoCity;
use App\Modules\Geo\Models\GeoCountry;
use App\Modules\Geo\Models\GeoRegion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GeoCity>
 */
class GeoCityFactory extends Factory
{
    protected $model = GeoCity::class;

    public function definition(): array
    {
        return [
            'geo_country_id' => GeoCountry::factory(),
            'geo_region_id'  => null,
            'title'          => fake()->city(),
            'latitude'       => fake()->latitude(),
            'longitude'      => fake()->longitude(),
            'rank'           => fake()->numberBetween(0, 10),
            'display'        => true,
        ];
    }

    public function forCountry(GeoCountry $country): static
    {
        return $this->state(fn(array $attributes) => [
            'geo_country_id' => $country->id,
        ]);
    }

    public function forRegion(GeoRegion $region): static
    {
        return $this->state(fn(array $attributes) => [
            'geo_country_id' => $region->geo_country_id,
            'geo_region_id'  => $region->id,
        ]);
    }
}
