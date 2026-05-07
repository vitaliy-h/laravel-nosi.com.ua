<?php

namespace App\Filament\Resources\News\Pages;

use App\Filament\Resources\News\NewsResource;
use Filament\Resources\Pages\ManageRecords;

class ManageNews extends ManageRecords
{
    protected static string $resource = NewsResource::class;

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
