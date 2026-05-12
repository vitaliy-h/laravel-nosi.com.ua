<?php

namespace App\Filament\Actions;

use App\Filament\Resources\Logs\LogResource;
use Filament\Actions\Action;

class ViewLogsAction
{
    public static function make(): Action
    {
        return Action::make(__('admin.actions.view_logs'))
            ->label(__('admin.actions.view_logs'))
            ->icon('heroicon-o-clock')
            ->url(fn($record) => LogResource::getUrl('index', [
                'filters[model_type][value]' => $record::class,
                'filters[model_id][model_id]' => $record->getKey(),
            ]),true);
    }
}
