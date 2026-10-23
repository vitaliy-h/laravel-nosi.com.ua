<?php

namespace App\Filament\Resources\Home\Reviews\Pages;

use App\Filament\Resources\Home\Reviews\ReviewResource;
use Filament\Resources\Pages\ManageRecords;

class ManageReviews extends ManageRecords
{
    protected static string $resource = ReviewResource::class;

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
