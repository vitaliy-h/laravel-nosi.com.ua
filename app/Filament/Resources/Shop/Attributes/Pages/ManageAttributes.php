<?php

namespace App\Filament\Resources\Shop\Attributes\Pages;

use App\Filament\Resources\Shop\Attributes\AttributeResource;
use Filament\Resources\Pages\ManageRecords;

class ManageAttributes extends ManageRecords
{
    protected static string $resource = AttributeResource::class;

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
