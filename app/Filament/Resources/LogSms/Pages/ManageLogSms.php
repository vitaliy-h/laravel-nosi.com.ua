<?php

namespace App\Filament\Resources\LogSms\Pages;

use App\Filament\Resources\LogSms\LogSmsResource;
use Filament\Resources\Pages\ManageRecords;

class ManageLogSms extends ManageRecords
{
    protected static string $resource = LogSmsResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTitle(): string
    {
        return '';
    }
}
