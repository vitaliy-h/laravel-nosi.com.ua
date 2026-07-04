<?php

namespace App\Filament\Resources\Basket\Pages;

use App\Filament\Resources\Basket\BasketResource;
use Filament\Resources\Pages\ManageRecords;

class ManageBaskets extends ManageRecords
{
    protected static string $resource = BasketResource::class;

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
