<?php

namespace App\Filament\Resources\SmsTemplates\Pages;

use App\Filament\Resources\SmsTemplates\SmsTemplateResource;
use Filament\Resources\Pages\ManageRecords;

class ManageSmsTemplates extends ManageRecords
{
    protected static string $resource = SmsTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTitle(): string
    {
        return '';
    }
}
