<?php

namespace App\Filament\Resources\Home\Faqs\Pages;

use App\Filament\Resources\Home\Faqs\FaqResource;
use Filament\Resources\Pages\ManageRecords;

class ManageFaqs extends ManageRecords
{
    protected static string $resource = FaqResource::class;

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
