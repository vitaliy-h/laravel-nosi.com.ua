<?php

namespace App\Filament\Resources\Geo\GeoCities\Pages;

use App\Filament\Resources\Geo\GeoCities\GeoCityResource;
use Filament\Resources\Pages\ManageRecords;

class ManageGeoCities extends ManageRecords
{
    protected static string $resource = GeoCityResource::class;

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
