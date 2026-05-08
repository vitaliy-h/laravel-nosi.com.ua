<?php

namespace App\Filament\Resources\Shop\Brands\Pages;

use App\Filament\Resources\Shop\Brands\BrandResource;
use Filament\Resources\Pages\ManageRecords;

class ManageBrands extends ManageRecords
{
    protected static string $resource = BrandResource::class;

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
