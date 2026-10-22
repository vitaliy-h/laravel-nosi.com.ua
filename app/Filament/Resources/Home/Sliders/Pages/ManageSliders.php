<?php

namespace App\Filament\Resources\Home\Sliders\Pages;

use App\Filament\Resources\Home\Sliders\SliderResource;
use Filament\Resources\Pages\ManageRecords;

class ManageSliders extends ManageRecords
{
    protected static string $resource = SliderResource::class;

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
