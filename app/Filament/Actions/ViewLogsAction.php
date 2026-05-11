<?php

namespace App\Filament\Actions;

use App\Filament\Resources\ActivityLogs\ActivityLogResource;
use Filament\Actions\Action;

class ViewLogsAction
{
    public static function make(): Action
    {
        return Action::make(__('admin.actions.view_logs'))
            ->label(__('admin.actions.view_logs'))
            ->icon('heroicon-o-clock')
            ->color('gray')
            ->url(fn($record) => ActivityLogResource::getUrl('index', [
                'filters[model_type][value]' => $record::class,
                'filters[model_id][model_id]' => $record->getKey(),
            ]));
    }
}
