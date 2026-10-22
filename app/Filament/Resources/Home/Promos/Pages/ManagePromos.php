<?php

namespace App\Filament\Resources\Home\Promos\Pages;

use App\Filament\Resources\Home\Promos\PromoResource;
use Filament\Resources\Pages\ManageRecords;

class ManagePromos extends ManageRecords
{
    protected static string $resource = PromoResource::class;

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
