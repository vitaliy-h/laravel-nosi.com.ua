<?php

namespace App\Filament\Resources\Shop\Promocodes\Pages;

use App\Filament\Resources\Shop\Promocodes\PromocodeResource;
use Filament\Resources\Pages\ManageRecords;

class ManagePromocodes extends ManageRecords
{
    protected static string $resource = PromocodeResource::class;

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
