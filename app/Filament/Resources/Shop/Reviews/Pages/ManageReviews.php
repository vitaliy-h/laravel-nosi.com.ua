<?php

namespace App\Filament\Resources\Shop\Reviews\Pages;

use App\Filament\Resources\Shop\Reviews\ReviewResource;
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
