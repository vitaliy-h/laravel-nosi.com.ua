<?php

namespace App\Filament\Resources\LogTelegram\Pages;

use App\Filament\Actions\ClearLogAction;
use App\Filament\Resources\LogTelegram\LogTelegramResource;
use App\Modules\Notification\Models\LogTelegram;
use Filament\Resources\Pages\ManageRecords;

class ManageLogTelegram extends ManageRecords
{
    protected static string $resource = LogTelegramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ClearLogAction::make(LogTelegram::class),
        ];
    }

    public function getTitle(): string
    {
        return '';
    }
}
