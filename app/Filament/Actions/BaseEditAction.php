<?php

namespace App\Filament\Actions;

use App\Filament\Traits\HasActivityLogsAction;
use Filament\Actions\Action;
use Filament\Actions\EditAction;


class BaseEditAction
{
    public static function make(): EditAction
    {
        return EditAction::make()
            ->iconButton()
            ->modalSubmitAction(false)
            ->modalCancelAction(false)
            ->extraModalFooterActions(fn (EditAction $action, $record) => [

                $action->makeModalSubmitAction(
                    'save',
                    arguments: ['close' => false],
                )->label(__('admin.actions.save')),

                $action->makeModalSubmitAction(
                    'saveAndClose',
                    arguments: ['close' => true],
                )->label(__('admin.actions.saveClose')),

                \Filament\Actions\Action::make('logs')
                    ->label('View logs')
                    ->icon('heroicon-o-clock')
                    ->outlined()
                    ->url(
                        \App\Filament\Resources\ActivityLogs\ActivityLogResource::getUrl('index', [
                            'filters[model_type][value]' => $record::class,
                            'filters[model_id][model_id]' => $record->getKey(),
                        ])
                    ,true)
                    ->extraAttributes([
                        'class' => 'ml-auto',
                    ]),
            ])
            ->action(function (array $data, array $arguments, $record, $action) :void {
                $record->update($data);

                if (!($arguments['close'] ?? true)) {
                    $action->callAfter();
                    $action->sendSuccessNotification();
                    $action->halt();
                    return;
                }
            });
    }
}
