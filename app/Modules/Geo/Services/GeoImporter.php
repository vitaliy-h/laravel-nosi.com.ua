<?php

namespace App\Modules\Geo\Services;

use App\Modules\Geo\Models\GeoCity;
use App\Modules\Geo\Models\GeoCountry;
use App\Modules\Geo\Models\GeoRegion;
use SimpleXMLElement;
use XMLReader;

class GeoImporter
{
    public function import(): void
    {
        $countryMap = $this->importCountries();

        if (config('geo.regions')) {
            $regionMap = $this->importRegions($countryMap);
        } else {
            $regionMap = [];
        }

        if (config('geo.cities')) {
            $this->importCities($countryMap, $regionMap);
        }
    }

    /**
     * COUNTRIES
     */
    protected function importCountries(): array
    {
        $allowed = config('geo.countries');

        $reader = new XMLReader;
        $reader->open(storage_path('app/public/geo/countries.xml'));

        $map = [];

        while ($reader->read()) {

            if ($reader->nodeType !== XMLReader::ELEMENT || $reader->name !== 'country') {
                continue;
            }

            $xml = new SimpleXMLElement($reader->readOuterXML());

            $iso2 = (string) $xml->iso2;

            if (!in_array($iso2, $allowed, true)) {
                continue;
            }
            $timezone = $this->resolveTimezone($xml);
            $country = GeoCountry::updateOrCreate(
                ['iso2' => $iso2],
                [
                    'iso3'          => (string) $xml->iso3,
                    'title'         => $this->getCurrentLocateValue($xml->translations, (string) $xml->name),
                    'phone_code'    => (string) $xml->phonecode,
                    'currency_code' => (string) $xml->currency,
                    'timezone'      => $timezone,
                ]
            );

            $map[(int) $xml->id] = $country->id;
        }

        $reader->close();

        return $map;
    }

    /**
     * REGIONS
     */
    protected function importRegions(array $countryMap): array
    {
        $reader = new XMLReader;
        $reader->open(storage_path('app/public/geo/regions.xml'));

        $map = [];

        while ($reader->read()) {

            if ($reader->nodeType !== XMLReader::ELEMENT || $reader->name !== 'state') {
                continue;
            }

            $xml = new SimpleXMLElement($reader->readOuterXML());

            $countryId = $countryMap[(int) $xml->country_id] ?? null;

            if (!$countryId) {
                continue;
            }

            $region = GeoRegion::updateOrCreate(
                [
                    'code' => (string) $xml->iso2 ?? null,
                ],
                [
                    'geo_country_id' => $countryId,
                    'title'          => $this->getCurrentLocateValue($xml->translations, (string) $xml->name),
                ]
            );

            $map[(int) $xml->id] = $region->id;
        }

        $reader->close();

        return $map;
    }

    /**
     * CITIES (BATCHED)
     */
    protected function importCities(array $countryMap, array $regionMap): void
    {
        $reader = new XMLReader;
        $reader->open(storage_path('app/public/geo/cities.xml'));

        $batch = [];

        while ($reader->read()) {

            if ($reader->nodeType !== XMLReader::ELEMENT || $reader->name !== 'city') {
                continue;
            }

            $xml = new SimpleXMLElement($reader->readOuterXML());

            $countryId = $countryMap[(int) $xml->country_id] ?? null;
            if (!$countryId) {
                continue;
            }

            $regionId = $regionMap[(int) $xml->state_id] ?? null;

            $batch[] = [
                'external_id'    => (int) $xml->id,
                'geo_country_id' => $countryId,
                'geo_region_id'  => $regionId,
                'title'          => $this->getCurrentLocateValue($xml->translations, (string) $xml->name),
                'latitude'       => (string) $xml->latitude,
                'longitude'      => (string) $xml->longitude,
                'created_at'     => now(),
                'updated_at'     => now(),
            ];

            if (count($batch) >= 1000) {
                $this->flushCities($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            $this->flushCities($batch);
        }

        $reader->close();
    }

    /**
     * UPSERT BATCH
     */
    protected function flushCities(array $batch): void
    {
        GeoCity::upsert(
            $batch,
            ['external_id'],
            [
                'geo_country_id',
                'geo_region_id',
                'title',
                'latitude',
                'longitude',
                'updated_at',
            ]
        );
    }

    /**
     * TRANSLATIONS PARSER
     */
    protected function getCurrentLocateValue($translations, $baseValue): string
    {
        if (!$translations) {
            return $baseValue;
        }

        $result = [];

        foreach ($translations->children() as $key => $value) {
            $result[$key] = (string) $value;
        }
        if (array_key_exists(config('app.locale'), $result)) {
            return $result[config('app.locale')];
        }

        return $baseValue;
    }

    private function resolveTimezone(SimpleXMLElement $xml): ?string
    {
        $capital = strtolower(trim((string) $xml->capital));

        if (!$capital) {
            return (string) $xml->timezones->zoneName ?? null;
        }

        $timezones = $xml->timezones;

        // if timezone only one
        if (isset($timezones->zoneName) && !isset($timezones[0])) {
            return (string) $timezones->zoneName;
        }

        $candidates = [];

        foreach ($timezones as $tz) {
            $name = (string) $tz->zoneName;

            // timezone for capital
            if (str_contains(strtolower($name), $capital)) {
                return $name;
            }

            $candidates[] = $name;
        }

        // fallback → first timezone
        return $candidates[0] ?? null;
    }
}
