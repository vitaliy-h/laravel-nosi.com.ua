<?php

namespace App\Filament\Resources\SocialProfiles\Pages;

use App\Filament\Resources\SocialProfiles\SocialProfileResource;
use Filament\Resources\Pages\ManageRecords;

class ManageSocialProfiles extends ManageRecords
{
    protected static string $resource = SocialProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTitle(): string
    {
        return '';
    }
}
