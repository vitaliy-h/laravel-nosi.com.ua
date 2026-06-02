<?php

namespace App\Filament\Resources\Geo\GeoCountries\Pages;

use App\Filament\Resources\Geo\GeoCountries\GeoCountryResource;
use Filament\Resources\Pages\ManageRecords;

class ManageGeoCountries extends ManageRecords
{
    protected static string $resource = GeoCountryResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }

    public function getTitle(): string
    {
        return '';
    }
}
