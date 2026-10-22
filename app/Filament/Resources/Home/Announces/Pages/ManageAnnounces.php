<?php

namespace App\Filament\Resources\Home\Announces\Pages;

use App\Filament\Resources\Home\Announces\AnnounceResource;
use Filament\Resources\Pages\ManageRecords;

class ManageAnnounces extends ManageRecords
{
    protected static string $resource = AnnounceResource::class;

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
