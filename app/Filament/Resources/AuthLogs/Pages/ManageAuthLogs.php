<?php

namespace App\Filament\Resources\AuthLogs\Pages;

use App\Filament\Actions\ClearLogAction;
use App\Filament\Resources\AuthLogs\AuthLogResource;
use App\Modules\Core\Models\AuthLog;
use Filament\Resources\Pages\ManageRecords;

class ManageAuthLogs extends ManageRecords
{
    protected static string $resource = AuthLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ClearLogAction::make(AuthLog::class),
        ];
    }

    public function getTitle(): string
    {
        return '';
    }
}
