<?php

namespace App\Filament\Resources\Geo\GeoRegions\Pages;

use App\Filament\Resources\Geo\GeoRegions\GeoRegionResource;
use Filament\Resources\Pages\ManageRecords;

class ManageGeoRegions extends ManageRecords
{
    protected static string $resource = GeoRegionResource::class;

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
