<?php

namespace App\Filament\Resources\LogSms\Pages;

use App\Filament\Actions\ClearLogAction;
use App\Filament\Resources\LogSms\LogSmsResource;
use App\Modules\Notification\Models\LogSms;
use Filament\Resources\Pages\ManageRecords;

class ManageLogSms extends ManageRecords
{
    protected static string $resource = LogSmsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ClearLogAction::make(LogSms::class),
        ];
    }

    public function getTitle(): string
    {
        return '';
    }
}
