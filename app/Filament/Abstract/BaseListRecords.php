<?php

namespace App\Filament\Abstract;

use Filament\Resources\Pages\ListRecords;

abstract class BaseListRecords extends ListRecords
{
    public function getHeader(): ?\Illuminate\Contracts\View\View
    {
        return null; // убирает H1 + header
    }

    protected function getHeaderActions(): array
    {
        return []; // убираем Create сверху страницы
    }
}
