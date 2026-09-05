<?php

namespace App\Filament\Resources\LogLetters\Pages;

use App\Filament\Actions\ClearLogAction;
use App\Filament\Resources\LogLetters\LogLetterResource;
use App\Modules\Notification\Models\LogLetter;
use Filament\Resources\Pages\ManageRecords;

class ManageLogLetters extends ManageRecords
{
    protected static string $resource = LogLetterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ClearLogAction::make(LogLetter::class),
        ];
    }

    public function getTitle(): string
    {
        return '';
    }
}
