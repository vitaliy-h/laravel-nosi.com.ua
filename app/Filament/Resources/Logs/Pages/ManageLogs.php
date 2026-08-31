<?php

namespace App\Filament\Resources\Logs\Pages;

use App\Filament\Resources\Logs\LogResource;
use App\Filament\Actions\ClearLogAction;
use App\Modules\Core\Models\Log;
use Filament\Resources\Pages\ManageRecords;

class ManageLogs extends ManageRecords
{
    protected static string $resource = LogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ClearLogAction::make(Log::class),
        ];
    }

    public function getTitle(): string
    {
        return '';
    }
}
